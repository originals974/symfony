<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Bridge\Doctrine\RegistryInterface;

//Custom classes

/**
 * Search Service
 *
 */
class LoggableService
{
    private $databaseEm;

    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     *
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->databaseEm = $registry->getManager('database');
    }

	/**
     * Get last $limit versions for $entity. 
     *
     * @param Mixed $entity
     * @param integer $limit
     *
     * @return array $formatedLogEntries
     */
	public function getFormatedLogEntries($entity, $limit = 5)
	{
		$logEntries = $this->databaseEm->getRepository('SLDataBundle:LogEntry')->getLogEntries($entity); 

		$formatedLogEntries = array(); 
		foreach(array_reverse($logEntries) as $logEntry){

			$entity = clone $entity; 

			$formatedLogEntry = array(); 
			$formatedLogEntry['version'] = $logEntry->getVersion();
			$formatedLogEntry['action'] = $logEntry->getAction();
			$formatedLogEntry['loggedAt'] = $logEntry->getLoggedAt();

			$entityLogData = $logEntry->getData();
			
			foreach($entityLogData as $key => $value){ 

				$entity->{"set".$key}($value);
			}

			$formatedLogEntry['data'] = $entity;

			$formatedLogEntries[] = $formatedLogEntry;
		}
				
		return array_slice(array_reverse($formatedLogEntries), 0, $limit); 
	}
}
