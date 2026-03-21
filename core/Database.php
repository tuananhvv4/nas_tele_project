<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?self $instance = null;
    private PDO $pdo;
    private array $queryLog = [];

    private string $table  = '';
    private array  $wheres = [];
    private array  $binds  = [];
    private ?int   $limitVal  = null;
    private ?int   $offsetVal = null;
    private string $orderByClause = '';
    private string $selectClause  = '*';
    private array  $joins = [];

    private function __construct()
    {
        $host    = $_ENV['DB_HOST']     ?? '127.0.0.1';
        $port    = $_ENV['DB_PORT']     ?? '3306';
        $dbname  = $_ENV['DB_DATABASE'] ?? '';
        $user    = $_ENV['DB_USERNAME'] ?? 'root';
        $pass    = $_ENV['DB_PASSWORD'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ── Query Builder ─────────────────────────────────────────────────────────

    public function table(string $table): self
    {
        $clone = clone $this;
        $clone->table        = $table;
        $clone->wheres       = [];
        $clone->binds        = [];
        $clone->limitVal     = null;
        $clone->offsetVal    = null;
        $clone->orderByClause = '';
        $clone->selectClause = '*';
        $clone->joins        = [];
        return $clone;
    }

    public function select(string $columns): self
    {
        $this->selectClause = $columns;
        return $this;
    }

    public function where(string $column, mixed $value, string $operator = '='): self
    {
        $placeholder = 'w_' . count($this->binds);
        $this->wheres[] = "{$column} {$operator} :{$placeholder}";
        $this->binds[$placeholder] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            $this->wheres[] = '1 = 0';
            return $this;
        }
        $placeholders = [];
        foreach ($values as $v) {
            $key = 'wi_' . count($this->binds);
            $placeholders[] = ":{$key}";
            $this->binds[$key] = $v;
        }
        $this->wheres[] = "{$column} IN (" . implode(',', $placeholders) . ")";
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = "{$column} IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = "{$column} IS NOT NULL";
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderByClause = "ORDER BY {$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limitVal = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offsetVal = $offset;
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    // ── Terminal Queries ──────────────────────────────────────────────────────

    public function get(): array
    {
        $sql = "SELECT {$this->selectClause} FROM {$this->table}";
        $sql .= $this->buildJoins();
        $sql .= $this->buildWhere();
        $sql .= $this->orderByClause ? " {$this->orderByClause}" : '';
        if ($this->limitVal !== null)  $sql .= " LIMIT {$this->limitVal}";
        if ($this->offsetVal !== null) $sql .= " OFFSET {$this->offsetVal}";

        return $this->query($sql, $this->binds);
    }

    public function first(): ?array
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    public function find(int|string $id, string $column = 'id'): ?array
    {
        return $this->where($column, $id)->first();
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table}";
        $sql .= $this->buildJoins();
        $sql .= $this->buildWhere();
        $row = $this->query($sql, $this->binds);
        return (int)($row[0]['cnt'] ?? 0);
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $total  = $this->count();
        $offset = ($page - 1) * $perPage;
        $items  = $this->limit($perPage)->offset($offset)->get();

        return [
            'data'         => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $perPage, $total),
        ];
    }

    public function insert(array $data): int
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql          = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(array $data): int
    {
        $sets = implode(', ', array_map(fn($k) => "{$k} = :set_{$k}", array_keys($data)));
        $sql  = "UPDATE {$this->table} SET {$sets}";
        $sql .= $this->buildWhere();

        $binds = array_merge(
            array_combine(array_map(fn($k) => "set_{$k}", array_keys($data)), $data),
            $this->binds
        );
        $stmt = $this->prepare($sql, $binds);
        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $sql  = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhere();
        $stmt = $this->prepare($sql, $this->binds);
        return $stmt->rowCount();
    }

    // ── Raw Queries ───────────────────────────────────────────────────────────

    public function query(string $sql, array $binds = []): array
    {
        $stmt = $this->prepare($sql, $binds);
        return $stmt->fetchAll();
    }

    public function statement(string $sql, array $binds = []): bool
    {
        $stmt = $this->prepare($sql, $binds);
        return $stmt !== false;
    }

    // ── Transactions ──────────────────────────────────────────────────────────

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function lastInsertId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    private function buildWhere(): string
    {
        return !empty($this->wheres) ? ' WHERE ' . implode(' AND ', $this->wheres) : '';
    }

    private function buildJoins(): string
    {
        return !empty($this->joins) ? ' ' . implode(' ', $this->joins) : '';
    }

    private function prepare(string $sql, array $binds): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        foreach ($binds as $key => $value) {
            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default         => PDO::PARAM_STR,
            };
            $stmt->bindValue(is_int($key) ? $key + 1 : ':' . $key, $value, $type);
        }
        $stmt->execute();
        return $stmt;
    }
}
