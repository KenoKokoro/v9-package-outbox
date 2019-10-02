<?php

namespace V9\Outbox\Contracts;

/**
 * Interface SubjectInstance
 * @package V9\Outbox\Contracts
 *          No strict types on return are used, since these methods are declared in Illuminate\Database\Eloquent\Model
 *          And they are not defined with strict return type there
 */
interface SubjectInstance
{
    /**
     * Get the id of the instance
     * @return string
     */
    public function getKey();

    /**
     * Get the type of the morph
     * @return string
     */
    public function getMorphClass();
}
