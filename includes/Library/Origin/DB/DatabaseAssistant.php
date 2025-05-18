<?php

declare(strict_types=1);

namespace Origin\DB;

use \DateTime;
use \Origin\Utilities\Settings;
use \ReflectionClass;

abstract class DatabaseAssistant implements \JsonSerializable {
    protected $database;
    protected $columns;
    protected $joins;
    protected $table;
    
    abstract public function ID($value = NULL);
    abstract public function Deleted($value = NULL);
    abstract public function Genesis(DateTime $value = NULL);
    abstract public function Mutation(DateTime $value = NULL);
    
    public function __construct() {
        $this->database = DB::Get(Settings::Get()->Value(['origin', 'default_database']));
    }
    
    public function jsonSerialize() {
        $return = $this->things;
        if (isset($return['ClassSpace'])) {
            unset($return['ClassSpace']);
        }
        
        return $this->things;
    }
    
    public function Table() {
        if ($this->table === NULL) {
            $reflection = new \ReflectionClass($this);
            $this->table = $reflection->getShortName();
        }
        
        return $this->table;
    }
    
    public function Columns() {
        $this->PopulateColumns();
        return array_keys($this->columns);
    }
    
    public function ColumnMap() {
        $this->PopulateColumns();
        return $this->columns;
    }
    
    public function Children() {
        return [];
    }
    
    protected $order_by = 'ID';
    public function OrderBy($value = NULL) {
        if ($value !== NULL) {
            $this->order_by = $value;
        }
        
        return $this->order_by;
    }
    
    protected $order_by_variables = [];
    public function OrderByVariables(array $variables = []) {
        if (!empty($variables)) {
            $this->order_by_variables = $variables;
        }
        
        return $this->order_by_variables;
    }
    
    public function NullColumns(array $columns) {
        foreach ($columns as $function) {
            if (method_exists($this, $function)) {
                $this->things[$function] = NULL;
            }
        }
        
        return TRUE;
    }
    
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
    
    public function FetchChildren() {
        Selector::Get()->FetchChildren(NULL, $this);
        return TRUE;
    }
    
    public function Joins() {
        return $this->joins;
    }
    
    public function Reset(array $columns = []) {
        foreach ($columns as $column) {
            $this->things[$column] = NULL;
        }
        
        return TRUE;
    }
    
    public function Update(array $columns = []) {
        if (empty($columns)) {
            $columns = $this->ColumnMap();
        }
        
        if (!empty($columns) && $this->ID() !== NULL) {
            $parameters = [];
            foreach ($columns as $name => $type) {
                if (is_numeric($name)) {
                    $name = $type;
                }
                
                if (isset($this->things[$name])) {
                    $parameters[$name] = $this->$name();
                    if ($this->$name() instanceof DateTime) {
                        $parameters[$name] = $this->$name()->format(DB::DEFAULT_DATE_FORMAT);
                    }

                    if ($type === 'Origin\Utilities\Bucket\Common::Binary') {
                        $parameters[$name] = (binary) $parameters[$name];
                    }

                    if ($type === 'Origin\Utilities\Bucket\Common::Boolean') {
                        if ($parameters[$name] === TRUE) {
                            $parameters[$name] = 1;
                        } elseif ($parameters[$name] === FALSE) {
                            $parameters[$name] = 0;
                        } else {
                            $parameters[$name] = NULL;
                        }
                    }
                }
            }

            $this->database->Update($this->Table(), $parameters, 'ID = :id', [':id' => $this->ID()]);
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function Insert(array $columns = [], $ignore = FALSE) {
        if (empty($columns)) {
            $columns = $this->ColumnMap();
        }
        
        if (!empty($columns) && $this->ID() === NULL) {
            $parameters = [];
            foreach ($columns as $name => $type) {
                if ($this->$name() !== NULL) {
                    if ($type === 'Origin\Utilities\Bucket\Common::Binary') {
                        $parameters[$name] = (binary) $this->$name();
                    } else {
                        $parameters[$name] = $this->$name();
                    }
                }
            }
            
            $this->database->Insert($this->Table(), $parameters, DB::DEFAULT_DATE_FORMAT, $ignore);
            $this->ID($this->database->LastID());
            if ($this->Genesis() === NULL) {
                $this->Genesis((new DateTime()));
            }
            
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function Delete() {
        $this->Deleted(TRUE);
        return $this->Update(['Deleted']);
    }
    
    public function Remove() {
        $this->database->Remove($this->Table(), 'ID = :id', [':id' => $this->ID()]);
        return TRUE;
    }
    
    public function Query(Parameters $parameters = NULL, array $columns = [], $joins = NULL, $first = FALSE, $children = FALSE) {
        if (empty($columns)) {
            $columns = $this->Columns();
        }
        
        if (empty($joins)) {
            $joins = $this->Joins();
        }
        
        $results = Selector::Get()->Query($this->Table(), $parameters, $columns, $joins);
        if ((count($results) === 1 && isset($results[0])) || ($first === TRUE && isset($results[0]))) {
            if ($children === TRUE) {
                $this->FetchChildren();
            }
            
            return $results[0];
        }
        
        return NULL;
    }
    
    public function QueryObject(Parameters $parameters = NULL, $children = FALSE, $first = FALSE) {
        $result = $this->Query($parameters, [], NULL, $first, $children);
        if ($result !== NULL) {
            return $this->ArrayToObject($result);
        }
        
        return FALSE;
    }
    
    public function QueryID($id = NULL, $deleted = FALSE) {
        $result = $this->Query((new Parameters(['Column' => 'ID', 'Value' => $id], ['Column' => 'Deleted', 'Value' => $deleted])));
        if ($result !== NULL) {
            return $this->ArrayToObject($result);
        }
        
        return FALSE;
    }
    
    public function ArrayToObject(array $array) {
        foreach ($array as $key => $value) {
            if (isset($this->ColumnMap()[$key]) && $value !== NULL) {
                switch($this->ColumnMap()[$key]) {
                    case 'Origin\Utilities\Bucket\Common::Date':
                        $this->$key((new DateTime($value)));
                        break;
                    case 'Origin\Utilities\Bucket\Common::Boolean':
                        $this->$key(($value === '1' || $value === 1) ? TRUE : FALSE);
                        break;
                    case 'Origin\Utilities\Bucket\Common::Hash':
                        $this->$key(json_decode($value, TRUE) ?? NULL);
                        break;
                    case 'Origin\Utilities\Bucket\Common::Decimal':
                        $this->$key((float) $value);
                        break;
                    case 'Origin\Utilities\Bucket\Common::Binary':
                        $this->$key((binary) $value);
                        break;
                    default:
                        $this->$key($value);
                        break;
                }
            }
        }
        
        return TRUE;
    }
    
    public function PopulateFromSource(array $array) {
        foreach ($array as $key => $value) {
            if (isset($this->ColumnMap()[$key]) && $value !== NULL) {
                $this->$key($value);
            }
        }
        
        return TRUE;
    }
}