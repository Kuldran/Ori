<?php

declare(strict_types=1);

namespace Origin\Utilities;

use \DateTime;

class Lock extends \Origin\Utilities\Types\Singleton {
    const LOCK_FOLDER = 'hidden/locks/';
    public function Prune() {
        if ($handle = opendir($this->GetLockFileDirectory())) {
            $count = 0;
            $total_pruned = 0;
            while (($file = readdir($handle)) !== FALSE) {
                if (strpos($file, '.lock') !== FALSE) {
                    $prune = FALSE;
                    $data = json_decode(file_get_contents($this->GetLockFileDirectory().$file), TRUE);
                    
                    if (empty($data) || $data === FALSE) {
                        $prune = TRUE;
                    } else {
                        if (isset($data['expiration_date'])) {
                            if (isset($data['expiration_date']['date'])) {
                                $date = DateTime::createFromFormat('Y-m-d H:i:s.u', $data['expiration_date']['date']);
                                if ($date->getTimestamp() < (new DateTime())->getTimestamp()) {
                                    $prune = TRUE;
                                }
                            } elseif ((new DateTime())->getTimestamp() > $data['expiration_date']) {
                                $prune = TRUE;
                            }
                        } elseif (isset($data['creation_date'])) {
                            if ((new DateTime())->modify('-1 day')->getTimestamp() > $data['creation_date']) {
                                $prune = TRUE;
                            }
                        } else {
                            $prune = TRUE;
                        }
                    }
                    
                    if ($prune === TRUE) {
                        $total_pruned++;
                        echo sprintf("Pruned lock file: %s\n", $file);
                        unlink($this->GetLockFileDirectory().$file);
                    }
                }
                
                usleep(5000);
                $count++;
            }
            
            closedir($handle);
            echo sprintf("Pruned %s files.\n", $total_pruned);
        } else {
            echo "Couldn't open lock folder.\n";
        }
    }
    
    public function RequestLock($name, array $parameters = []) {
        if (!$this->LockExists($name)) {
            $parameters['creation_date'] = (new DateTime())->getTimestamp();
            file_put_contents($this->GetLockFileName($name), json_encode($parameters));
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function ReleaseLock($name) {
        return !file_exists($this->GetLockFileName($name)) || unlink($this->GetLockFileName($name));
    }
    
    public function LockExists($name) {
        return file_exists($this->GetLockFileName($name));
    }
    
    public function LockAge($name) {
        if ($this->LockExists($name)) {
            return (new DateTime())->setTimestamp(filemtime($this->GetLockFileName($name)));
        }
    }
    
    public function GetContent($name) {
        if ($this->LockExists($name)) {
            return json_decode(file_get_contents($this->GetLockFileName($name)), TRUE);
        }
    }
    
    private function GetLockFileName($name) {
        return $this->GetLockFileDirectory().$name.'.lock';
    }

    private function GetLockFileDirectory() {
        return getcwd().DIRECTORY_SEPARATOR.static::LOCK_FOLDER;
    }
}
