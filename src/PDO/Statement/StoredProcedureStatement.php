<?php

/**
 * @license MIT
 * @license http://opensource.org/licenses/MIT
 */
namespace Slim\PDO\Statement;

use Slim\PDO\Database;

/**
 * Class StoredProcedureStatement.
 *
 * This class is created to execute stored procedures,
 * assumed is that everything is handled in the stored procedure. That is why
 * there is no groupBy, having, ... . Only the function from the base class are
 * available, but think twice before using them. It depends on you database if
 * it can handle the statements.
 *
 * Because not all databases use `exec` to execute a stored procedure, it was
 * necessary to make it flexible. the correct statement MUST be passed to the
 * constructor. While checking for the execute statements id found these:
 * `exec`, `call`, `execute`, `execute procedure`. This is only the list that i
 * found, there can be more execute statements.
 *
 *
 * @author Fabian de Laender <fabian@faapz.nl>
 *
 * @author Edwin Huijsing <edwin.huijsing@2be-it.com>
 */
class StoredProcedureStatement extends StatementContainer
{
    /**
     * @var string spName Name of the stored procedure
     */
    protected $storedProcedureName;

    /**
     * @var string execText store first part off the query
     *                      like: exec, execute, call, ...
     */
    protected $execText;

    /**
     * Constructor.
     *
     * @param Database $dbh
     * @param string   $execText
     */
    public function __construct(Database $dbh, $execText)
    {
        parent::__construct($dbh);

        $this->execText = $execText;
    }

    /**
    *   @param string spName Name of the stored procedure.
    *
    *   @return $this
    */
    public function storedprocedureName($storedProcedureName)
    {
        $this->storedProcedureName = $storedProcedureName;

        return $this;
    }

    /**
     * Set that values.
     *
     * The values passed MUST be at least a value and an empty array [value =>[]]
     * The data_type is an [PDO::PARAM_* constants](http://php.net/manual/en/pdo.constants.php)
     * value, the length MUST be an int.
     * Option drivers are no supported.
     *
     * @param array $values array of arrays that are build like this
     *                      [value => [data_type, length]]
     *
     * @return $this
     */
    public function values(array $values)
    {
        $this->setValues($values);

        $this->setPlaceholders(array_keys($values));

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (empty($this->storedProcedureName)) {
            trigger_error('No stored procedure name is set for execution.', E_USER_ERROR);
        }

        if (empty($this->values)) {
            trigger_error('Missing values for insertion', E_USER_ERROR);
        }

        $sql = $this->execText;
        $sql .= ' ' . $this->spName;
        $sql .= $this->getPlaceholders();

        return $sql;
    }

    /**
     * @return \PDOStatement
     */
    public function execute()
    {
        $stmt = $this->getStatement();
        $stmt = $this->bindValue($stmt);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Bind the values to the $stmt.
     *
     * If the data_type or length is found, it will be set.
     * It is assumed that length only is set if the data_type is set.
     *
     * @param $stmt \PDOStatement
     *
     * @return \PDOStatement
     */
    protected function bindValue(\PDOStatement $stmt)
    {
        private const DATE_TYPE = 'data_type';
        private const length = 'length';

        $index = 0;
        $values = $this->values;
        $valuesCount = count($values);

        for ($i=0; $i < $valuesCount; $i++) {
            $key = key($values[$i]);
            $length = 0;
            $type = '';
            $value = null;

            // Check if there is a value in the array, if so use it.
            if (count(array_values($values[$i])[0]) > 0) {
                $value = array_values($values[$i])[0];

                if (key_exists(DATA_TYPE, $value)) {
                    $type = $value[DATA_TYPE];
                }

                if (key_exists(LENGTH, $value)) {
                    $length = $value[LENGTH];
                }
            }

            // Update the params index, it MUST start with 1 not with 0.
            $index = $i + 1;

            // Add the values, bindValue is used because bindParam
            // throws exceptions.
            if (!empty($type) && $length > 0) {
                $stmt->bindValue($index, $key, $type, $length);
            } elseif (!empty($type)) {
                $stmt->bindValue($index, $key, $type);
            } else {
                $stmt->bindValue($index, $key);
            }
        }

        return $stmt;
    }
}
