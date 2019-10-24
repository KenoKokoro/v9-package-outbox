<?php

namespace V9\Outbox\Module\Schedule;

use Closure;
use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;
use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\DAL\Outbox\OutboxRepository;
use V9\Outbox\Models\Outbox;
use V9\Outbox\Module\Channel\DTO\Channel;
use V9\Outbox\Module\Channel\DTO\ChannelCollectionInterface;
use V9\Outbox\Module\Exceptions\UnsupportedChannelException;

class V9Schedule implements ScheduleInterface
{
    private OutboxRepository $repository;

    private ChannelCollectionInterface $supportedChannels;

    private Encrypter $encrypter;

    private Container $container;

    public function __construct(
        OutboxRepository $repository,
        ChannelCollectionInterface $supportedChannels,
        Encrypter $encrypter,
        Container $container
    ) {
        $this->repository = $repository;
        $this->supportedChannels = $supportedChannels;
        $this->encrypter = $encrypter;
        $this->container = $container;
    }

    public function put(
        OutboxInstance $instance,
        string $channel,
        SubjectInstance $subject,
        ReceiverInstance $receiver,
        Carbon $sendAt = null
    ): Outbox {
        $channel = $this->getChannel($channel);
        if (is_null($sendAt)) {
            $sendAt = Carbon::now();
        }

        /** @var Outbox $outbox */
        $outbox = $this->repository->create([
            'channel' => $channel->getKey(),
            'content' => $this->encryptContent($instance),
            'send_at' => $sendAt->format('Y-m-d H:i:s'),
            'sent_at' => null,
            'status' => Outbox::STATUS_PENDING,
            'subject_id' => $subject->getKey(),
            'subject_type' => $subject->getMorphClass(),
            'receiver_id' => $receiver->getKey(),
            'receiver_type' => $receiver->getMorphClass(),
            'try' => 0,
            'error' => null,
        ]);

        return $outbox;
    }

    public function done(Outbox $outbox): void
    {
        $this->repository->update($outbox, [
            'sent_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'status' => Outbox::STATUS_DONE,
            'error' => null,
        ]);
    }

    public function processing(Outbox $outbox): void
    {
        $this->repository->update($outbox, [
            'status' => Outbox::STATUS_RUNNING,
            'error' => null,
            'try' => ($outbox->try + 1),
        ]);
    }

    public function error(Outbox $outbox, Exception $exception): void
    {
        $this->repository->update($outbox, [
            'status' => Outbox::STATUS_ERROR,
            'error' => $exception->getMessage(),
        ]);
    }

    public function getScheduled(int $tries, Carbon $endDate, ?Carbon $startDate = null): Collection
    {
        $query = $this->repository
            ->newQuery()
            ->where('try', '<', $tries)
            ->whereIn('status', [Outbox::STATUS_PENDING, Outbox::STATUS_ERROR])
            ->where('send_at', '<=', $endDate->format('Y-m-d H:i:s'))
            ->orderBy(Outbox::CREATED_AT, 'asc');

        if (!is_null($startDate)) {
            $query->where('send_at', '>=', $startDate->format('Y-m-d H:i:s'));
        }

        return $query->get();
    }

    public function send(Outbox $outbox): void
    {
        $this->processing($outbox);
        $channel = $this->getChannel($outbox->channel);
        $instance = $this->decryptContent($outbox->content);
        $action = $channel->getAction();

        try {
            call_user_func($action, $instance, ...$this->resolveDependency($action));
            $this->done($outbox);
        } catch (\Exception $exception) {
            $this->error($outbox, $exception);
            throw $exception;
        }
    }

    /**
     * Check if the given channel key is supported
     * @param string $channel
     * @return Channel
     */
    private function getChannel(string $channel): Channel
    {
        if (!$this->supportedChannels->has($channel)) {
            throw new UnsupportedChannelException($channel);
        }

        return $this->supportedChannels->get($channel);
    }

    /**
     * Encrypt the outbox content instance
     * @param OutboxInstance $content
     * @return string
     */
    private function encryptContent(OutboxInstance $content): string
    {
        return $this->encrypter->encrypt(base64_encode(serialize($content)));
    }

    /**
     * Get the instance from the encrypted content
     * @param string $content
     * @return OutboxInstance
     */
    public function decryptContent(string $content): OutboxInstance
    {
        return unserialize(base64_decode($this->encrypter->decrypt($content)));
    }

    /**
     * @param Closure $action
     * @return array
     * @throws ReflectionException
     */
    private function resolveDependency(Closure $action): array
    {
        $parameters = (new ReflectionFunction($action))->getParameters();
        // First argument we always send as the OutboxInstance
        unset($parameters[0]);

        return array_map(function(ReflectionParameter $parameter) {
            return $this->container->make($parameter->getClass()->getName());
        }, $parameters);
    }
}
