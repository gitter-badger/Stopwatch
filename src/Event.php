<?php

namespace Codervio\Stopwatch;

use Codervio\Stopwatch\Exception\EventException;

class Event
{
    private $event = array();

    private $eventconsume = array();

    private $tasks = 0;

    private $taskName = null;

    private $triggeredEventType = null;

    private $triggeredEventName = null;

    public function start($time, $eventName)
    {
        $this->taskName = $eventName;

        $this->eventconsume[$eventName]['start'] = $time;
        $this->eventconsume[$eventName]['duration'] = 0;
        $this->eventconsume[$eventName]['type'] = 'runned';

        $this->event[$eventName]['start'] = $time;
        time_nanosleep(0, 1);

        $this->triggeredEventType = __FUNCTION__;
        $this->triggeredEventName = $eventName;

        return $this;
    }

    public function stop($time, $eventName)
    {
        $this->event[$eventName]['end'] = $time;
        $consumption[$eventName] = new Consumption($time, $this->event[$eventName]['start']);

        $this->eventconsume[$eventName]['duration'] = $consumption[$eventName]->getConsumptionTime();
        $this->eventconsume[$eventName]['start'] = $consumption[$eventName]->getStart();
        $this->eventconsume[$eventName]['stop'] = $consumption[$eventName]->getStop();
        $this->eventconsume[$eventName]['type'] = 'runned';

        ++$this->tasks;

        $this->triggeredEventType = __FUNCTION__;
        $this->triggeredEventName = $eventName;

        return $this;
    }

    public function pause($time, $eventName)
    {
        $this->event[$eventName]['start'] = $time;

        $this->eventconsume[$eventName]['start'] = $time;
        $this->eventconsume[$eventName]['duration'] = 0;
        $this->eventconsume[$eventName]['type'] = 'pause';

        time_nanosleep(0, 1);

        $this->triggeredEventType = __FUNCTION__;
        $this->triggeredEventName = $eventName;

        return $this;
    }

    public function unpause($time, $eventName)
    {
        $this->event[$eventName]['end'] = $time;
        $consumption[$eventName] = new Consumption($time, $this->event[$eventName]['start']);

        $this->eventconsume[$eventName]['duration'] = $consumption[$eventName]->getConsumptionTime();
        $this->eventconsume[$eventName]['start'] = $consumption[$eventName]->getStart();
        $this->eventconsume[$eventName]['stop'] = $consumption[$eventName]->getStop();
        $this->eventconsume[$eventName]['type'] = 'pause';

        $this->triggeredEventType = __FUNCTION__;
        $this->triggeredEventName = $eventName;

        ++$this->tasks;

        return $this;
    }

    public function lastEvent()
    {
        return [
            'type' => $this->triggeredEventType,
            'name' => $this->triggeredEventName
        ];
    }

    public function getTaskCount()
    {
        return $this->tasks;
    }

    public function getTaskName()
    {
        return $this->taskName;
    }

    public function getTimeBorn()
    {
        $firstEvent = reset($this->event);

        return $firstEvent['start'];
    }

    public function getDuration($eventName = null)
    {
        $calculate = 0;

        $eventclone = clone $this;

        if (!is_null($eventName)) {
            return end($eventclone->event[$eventName]) - reset($eventclone->event[$eventName]);
        }

        foreach ($eventclone->eventconsume as $events) {
            if ($events['type'] === 'runned') {
                $calculate += $events['duration'];
            }
        }

        return $calculate;
    }

    public function getRawData()
    {
        $eventclone = clone $this;

        $cnt = 1;

        foreach ($eventclone->event as $eventName => $times) {

            foreach ($times as $order => $time) {

                $collects[$cnt][$time][] = array($order, $eventName, $eventclone->eventconsume[$eventName]);
                $cnt++;
            }

        }

        return $collects;
    }

    public function getEvent($eventName)
    {
        if (!isset($this->eventconsume[$eventName])) {
            throw new EventException(sprintf('There are no %s event or is not started.', $eventName));
        }

        return new EventConsume($this->eventconsume[$eventName]);
    }

    public function eventCheck($eventName)
    {
        return isset($this->event[$eventName]);
    }
}
