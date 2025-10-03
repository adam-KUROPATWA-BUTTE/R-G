<?php

namespace App\Core;

/**
 * Base Model class - provides database interaction methods
 */
abstract class Model {
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    /**
     * Get database connection
     */
    protected function db(): \PDO {
        return \db();
    }
    
    /**
     * Find a record by ID
     */
    public function find(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Find all records
     */
    public function all(array $conditions = [], int $limit = 0, int $offset = 0): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
            if ($offset > 0) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Find records matching conditions
     */
    public function where(array $conditions): array {
        return $this->all($conditions);
    }
    
    /**
     * Find first record matching conditions
     */
    public function first(array $conditions): ?array {
        $results = $this->all($conditions, 1);
        return $results[0] ?? null;
    }
    
    /**
     * Create a new record
     */
    public function create(array $data): int {
        $data = $this->filterFillable($data);
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int)$this->db()->lastInsertId();
    }
    
    /**
     * Update a record by ID
     */
    public function update(int $id, array $data): bool {
        $data = $this->filterFillable($data);
        
        $sets = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " 
                WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete a record by ID
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Count records
     */
    public function count(array $conditions = []): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Filter data to only fillable fields
     */
    protected function filterFillable(array $data): array {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_filter($data, function($key) {
            return in_array($key, $this->fillable);
        }, ARRAY_FILTER_USE_KEY);
    }
}
