<?php

declare(strict_types=1);

namespace Origin\DB;

class Parameter {
    private $value;
    private $nullable;
    private $column;
    private $limit_start;
    private $limit_count;
    private $humanreadable;
    private $comparitor = '=';
    private $conjunction = 'AND';
    private $multi_conjunction = 'OR';
    private $subparameters = [];
    
    public function __construct(array $array = []) {
        if (!empty($array)) {
            $this->Column((isset($array['Column']) ? $array['Column'] : NULL));
            $this->Value((isset($array['Value']) ? $array['Value'] : NULL));
            $this->Comparitor((isset($array['Comparitor']) ? $array['Comparitor'] : NULL));
            $this->Conjunction((isset($array['Conjunction']) ? $array['Conjunction'] : NULL));
            $this->HumanReadable((isset($array['HumanReadable']) ? $array['HumanReadable'] : NULL));
            $this->Subparameters((isset($array['SubParameters']) ? $array['SubParameters'] : NULL));
            $this->MultiConjunction((isset($array['MultiConjunction']) ? $array['MultiConjunction'] : NULL));
            $this->LimitStart((isset($array['LimitStart']) ? $array['LimitStart'] : NULL));
            $this->LimitCount((isset($array['LimitCount']) ? $array['LimitCount'] : NULL));
            $this->Nullable((isset($array['Nullable']) ? $array['Nullable'] : FALSE));
        }
    }
    
    public function Column($column = NULL) {
        if ($column !== NULL) {
            $this->column = $column;
        }
        
        return $this->column;
    }
    
    public function Value($value = NULL) {
        if ($value !== NULL) {
            $this->value = $value;
        }
        
        return $this->value;
    }
    
    public function LimitStart($value = NULL) {
        if ($value !== NULL) {
            $this->limit_start = $value;
        }
        
        return $this->limit_start;
    }
    
    public function LimitCount($value = NULL) {
        if ($value !== NULL) {
            $this->limit_count = $value;
        }
        
        return $this->limit_count;
    }

    public function Comparitor($comparitor = NULL) {
        if ($comparitor !== NULL) {
            $this->comparitor = $comparitor;
        }
        
        return $this->comparitor;
    }
    
    public function Conjunction($conjunction = NULL) {
        if ($conjunction !== NULL) {
            $this->conjunction = $conjunction;
        }
        
        return $this->conjunction;
    }
    
    public function MultiConjunction($conjunction = NULL) {
        if ($conjunction !== NULL) {
            $this->multi_conjunction = $conjunction;
        }
        
        return $this->multi_conjunction;
    }
    
    public function SubParameters(Parameters $subparameters = NULL) {
        if (!empty($subparameters)) {
            $this->subparameters = $subparameters;
        }
        
        return $this->subparameters;
    }
    
    public function HumanReadable($value = NULL) {
        if (!empty($value)) {
            $this->humanreadable = $value;
        }
        
        return $this->humanreadable;
    }
    
    public function Nullable($value = NULL) {
        if (!empty($value)) {
            $this->nullable = $value;
        }
        
        return $this->nullable;
    }
}