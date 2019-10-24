<?php

namespace V9\Outbox\Command;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Psr\Log\LoggerInterface;
use Throwable;
use V9\Outbox\Models\Outbox;
use V9\Outbox\Module\Schedule\ScheduleInterface;

class SendOutbox extends Command
{
    const DEFAULT_TRIES = 3;

    protected $signature = "cron:outbox:send 
                            {--tries=" . self::DEFAULT_TRIES . " : How much times to try before starting to ignore the record (< operator used)}
                            {--start-date= : Start date for the send_at command (>= operator used) [Y-m-d H:i:s]}
                            {--end-date= : End date for the send_at command (<= operator used) [Y-m-d H:i:s]}";

    protected $description = "Take the messages from the outbox (outbox) and send them";

    private ScheduleInterface $scheduler;

    private LoggerInterface $logger;

    public function __construct(
        ScheduleInterface $scheduler,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->scheduler = $scheduler;
        $this->logger = $logger;
    }

    /**
     * Run the job
     * @throws Exception
     */
    public function handle(): void
    {
        $outboxRecords = $this->getScheduledOutboxRecords();

        /** @var Outbox $outbox */
        foreach ($outboxRecords as $outbox) {
            try {
                $this->log("Sending", $outbox);
                $this->send($outbox);
            } catch (Throwable $exception) {
                $this->scheduler->error($outbox, $exception);
                $this->logger->error($exception);
                $this->log("Error while sending", $outbox);
            }
        }
    }

    /**
     * @param Outbox $outbox
     * @throws Throwable
     */
    private function send(Outbox $outbox): void
    {
        $this->scheduler->send($outbox);
        $this->log("Successfully sent", $outbox);
    }

    /**
     * Get the outbox records that should be sent
     * @return Collection
     * @throws Exception
     */
    private function getScheduledOutboxRecords(): Collection
    {
        $startDate = $this->getStartDateValue();
        $endDate = $this->getEndDateValue();
        $tries = $this->getTriesValue();

        $results = $this->scheduler->getScheduled($tries, $endDate, $startDate);
        $message = "[OUTBOX] Results ({$results->count()})";
        $this->logger->info($message);

        return $results;
    }

    /**
     * @return Carbon|null
     * @throws Exception
     */
    private function getStartDateValue(): ?Carbon
    {
        if (!($date = $this->option('start-date'))) {
            return null;
        }

        return $this->getDateValue($date);
    }

    /**
     * @return Carbon
     * @throws Exception
     */
    private function getEndDateValue(): Carbon
    {
        if (!($date = $this->option('end-date'))) {
            return Carbon::now();
        }

        return $this->getDateValue($date);
    }

    /**
     * @param string $date
     * @return Carbon
     * @throws Exception
     */
    private function getDateValue(string $date): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @return int
     */
    private function getTriesValue(): int
    {
        $tries = (int)$this->option('tries');

        if (!$tries) {
            return self::DEFAULT_TRIES;
        }

        return $tries;
    }

    /**
     * Simple debug messages
     * @param string $action
     * @param Outbox $outbox
     */
    private function log(string $action, Outbox $outbox): void
    {
        $message = "{$action} outbox [{$outbox->getKey() }]. 
                    Subject [{$outbox->subject_id}, {$outbox->subject_type}];
                    Receiver [{$outbox->receiver_id}, {$outbox->receiver_type}]";
        $this->logger->info("[OUTBOX] {$message}");
    }
}
