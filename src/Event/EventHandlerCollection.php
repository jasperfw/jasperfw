<?php
namespace JasperFW\JasperFW\Event;

/**
 * Class EventHandlerCollection
 *
 * Container for event handlers.
 *
 * @package JasperFW\JasperFW\Event
 */
class EventHandlerCollection
{
    /** @var EventHandler[] Array of callbacks */
    protected array $members;

    /**
     * Add an event hanlder to the collection
     *
     * @param EventHandler $eventHandler
     */
    public function addItem(EventHandler $eventHandler): void
    {
        $this->members[] = $eventHandler;
    }

    /**
     * Loop through and execute each member of the collection
     *
     * @param string $event
     */
    public function execute(string $event): void
    {
        foreach ($this->members as $member) {
            if ($member->getEvent() === $event) {
                $member->execute();
            }
        }
    }
}
