<?php

namespace SL\CoreBundle\Services;

//Symfony classes


//Custom classes
use SL\CoreBundle\Entity\Object;

/**
 * Search Service
 *
 */
class LoggableService
{
	/**
     * Convert $logEntries to an array 
     * with all versions data for an entity
     *
     * @param array $logEntries
     * @param Object $object
     *
     * @return array $formatLogEntries
     */
	public function formatLogEntries(array $logEntries, Object $object)
	{
		$formatLogEntries = array(); 
		$formatLogEntry = array(); 
		foreach(array_reverse($logEntries) as $logEntry){
			
			$formatLogEntry['version'] = array(
				'updated' => false, 
				'value' => $logEntry->getVersion(),
				); 
			$formatLogEntry['action'] = array(
				'updated' => false, 
				'value' => $logEntry->getAction(),
				); 
			
			$formatLogEntry['loggedAt'] = array(
				'updated' => false, 
				'value' => $logEntry->getLoggedAt(),
				); 

			$entityLogData = $logEntry->getData();
			foreach($object->getProperties() as $property){

				if(array_key_exists($property->getTechnicalName(), $entityLogData)) {
					
					if(array_key_exists($property->getTechnicalName(), $formatLogEntry)){

						if($formatLogEntry[$property->getTechnicalName()]['value'] === $entityLogData[$property->getTechnicalName()]){

							$formatLogEntry[$property->getTechnicalName()]['updated'] = false;
						}
						else{
							$formatLogEntry[$property->getTechnicalName()] = array(
								'updated' => true,
								'value' => $entityLogData[$property->getTechnicalName()],
							); 
						}
					}
					else{
						$formatLogEntry[$property->getTechnicalName()] = array(
							'updated' => false,
							'value' => $entityLogData[$property->getTechnicalName()],
						); 
					}
				}
				else {
					if(array_key_exists($property->getTechnicalName(), $formatLogEntry)){
						$formatLogEntry[$property->getTechnicalName()]['updated'] = false; 	 	
					}
					else {
						$formatLogEntry[$property->getTechnicalName()] = array(
							'updated' => false,
							'value' => '',
						);
					}
				}
			}	

			$formatLogEntries[] = $formatLogEntry;
		}

		return array_reverse($formatLogEntries); 
	}

}
