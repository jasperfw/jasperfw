<?php
namespace WigeDev\JasperCore\Event;

use WigeDev\JasperFarm\Utility\Collection;

class EventHandlerCollection extends Collection
{
    /** @var EventHandler[] Array of callbacks */
    protected $members;

    /**
     * Loop through and execute each member of the collection
     * @param string $event
     */
    public function execute(string $event)
    {
        foreach ($this->members as $member) {
            if ($member->getEvent() === $event) {
                $member->execute();
            }
        }
    }
}