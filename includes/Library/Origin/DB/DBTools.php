<?php

declare(strict_types=1);

namespace Origin\DB;

use \DateTime;
use \ReflectionClass;

trait DBTools {
    public function ColumnMap() {
        $this->PopulateColumns();
        return $this->columns;
    }
    
    public function ArrayToObject(array $array) {
        foreach ($array as $key => $value) {
            if (isset($this->ColumnMap()[$key]) && $value !== NULL) {
                switch($this->ColumnMap()[$key]) {
                    case 'Origin\Utilities\Bucket\Common::Date':
                        if (is_numeric($value)) {
                            $this->$key((new DateTime()));
                            $this->$key()->setTimestamp($value);
                        } else {
                            $this->$key((new DateTime($value)));
                        }
                        break;
                    case 'Origin\Utilities\Bucket\Common::Boolean':
                        $this->$key(($value === '1' || $value === 1) ? TRUE : FALSE);
                        break;
                    case 'Origin\Utilities\Bucket\Common::Hash':
                        $result = json_decode($value, TRUE);
                        if ($result !== NULL && is_array($result)) {
                            $this->$key($result);
                        } else {
                            $result = [];
                            $prepare = explode("\n", $value);
                            foreach ($prepare as $id => $row) {
                                $result[str_replace('\r', '', str_replace("\r", '', $id))] = str_replace('\r', '', str_replace("\r", '', $row));
                            }
                            
                            $this->$key($result);
                        }
                        break;
                    case 'Origin\Utilities\Bucket\Common::Float':
                        $this->$key((float) ($value !== '' ? $value : NULL));
                        break;
                    default:
                        $this->$key($value !== '' ? $value : NULL);
                        break;
                }
            }
        }
        
        return TRUE;
    }
    
    protected $columns;
    protected function PopulateColumns() {
        if ($this->columns === NULL) {
            $this->columns = $this->GetTraits((new \ReflectionClass($this)));
            
            // Remove objects.
            foreach ($this->columns as $name => $column) {
                if (strpos($column, 'Objects') !== FALSE) {
                    unset($this->columns[$name]);
                }
            }
        }
    }
    
    private function GetTraits(ReflectionClass $class) {
        $columns = [];
        $class_columns = $class->getTraitAliases();
        if (!empty($class_columns)) {
            $columns = array_merge($columns, $class_columns);
        }
        
        if ($class->getParentClass() !== FALSE) {
            $columns = array_merge($columns, $this->GetTraits($class->getParentClass()));
        }
        
        return $columns;
    }
}