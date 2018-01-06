<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Apigility;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Select;

/**
 *    SELECT
    `o1`.`ID` AS `ID`,
    `o1`.`ClientID` AS `ClientID`,
    `o1`.`OrderNumber` AS `OrderNumber`,
    `o1`.`OrderType` AS `OrderType`,
    `o1`.`ColorID` AS `ColorID`,
    `o1`.`Discount` AS `Discount`,
    `o1`.`Amount` AS `Amount`,
    `o1`.`DeadLine` AS `DeadLine`,
    `o1`.`DepartureDate` AS `DepartureDate`,
    `o1`.`DateIssue` AS `DateIssue`,
    `o1`.`TypeIssue` AS `TypeIssue`,
    `o1`.`Notes` AS `Notes`,
    IF(`o2`.`Status`,
    `o2`.`Status`,
    `o1`.`Status`) AS `Status`,
    IF(`o2`.`Version`,
    `o2`.`Version`,
    `o1`.`Version`) AS `Version`,
    `o1`.`AddTime` AS `AddTime`,
    `o1`.`Dead` AS `Dead`
    FROM
    ((`revita.dev`.`tblorders` `o1`
    LEFT JOIN `revita.dev`.`tblorders` `o2` ON (((`o1`.`ID` = `o2`.`ID`)
    AND ((`o1`.`Version` + 1) = `o2`.`Version`))))
    LEFT JOIN `revita.dev`.`tblorders` `o3` ON (((`o2`.`ID` = `o3`.`ID`)
    AND ((`o2`.`Version` + 1) = `o3`.`Version`))))
    WHERE
    (ISNULL(`o3`.`ID`)
    AND (`o1`.`Status` IN ('ADDED' , 'READY', 'DELETED')))
    ORDER BY `o1`.`ID` DESC
 * Class VersionableResource
 * @package MSBios\Apigility
 */
class VersionableResource extends Resource
{
    /** @var string */
    protected $statusName = 'Status';

    /** @var string */
    protected $versionName = 'Version';

    /**
     * @param int|string $id
     * @return mixed
     */
    public function fetch($id)
    {
        return $this->fetchRowVersion($id);
    }

    /**
     * @param array|object $data
     * @return array
     */
    public function create($data)
    {
        /** @var array $data */
        $data = $this->retrieveData($data);
        $this->table->insert($data);
        return $this->fetch($this->table->getLastInsertValue());
    }

    /**
     * @param int|string $id
     * @param array|object $data
     * @return array
     */
    public function update($id, $data)
    {
        /** @var array $data */
        $data = $this->retrieveData($data);

        /** @var array $params */
        $params = [
            $this->identifierName => $id,
            $this->versionName => $data[$this->versionName]
        ];

        switch ($data[$this->statusName]) {
            case 'MODIFIED':

                /** @var \ArrayObject $row */
                $row = $this->fetchRowVersion($id);

                switch ($row[$this->statusName]) {
                    case 'DRAFT':
                        $data[$this->statusName] = 'ADDED';
                        $data[$this->identifierName] = $id;
                        $data[$this->versionName]++;
                        $this->table->insert($data);
                        break;
                    case 'READY':
                        $data[$this->identifierName] = $id;
                        $data[$this->versionName]++;
                        $this->table->insert($data);
                        break;
                    case 'ADDED':
                    case 'MODIFIED':
                        $data[$this->statusName] = $row[$this->statusName];
                        $data[$this->versionName] = $row[$this->versionName];
                        $this->table->update($data, $params);
                        break;
                }

                break;

            case 'REMOVAL':
                $data[$this->identifierName] = $id;
                $data[$this->versionName]++;
                $this->table->insert($data);
                break;

            case 'CONFIRM':

                /** @var \ArrayObject $row */
                $row = $this->fetchRowVersion($id);
                switch ($row[$this->statusName]) {
                    case 'ADDED':
                    case 'MODIFIED':
                        $data = (array)$row;
                        $data[$this->statusName] = 'READY';
                        break;

                    case 'REMOVAL':
                        $data = (array)$row;
                        $data[$this->statusName] = 'DELETED';
                        break;
                }

                $data[$this->identifierName] = $id;
                $data[$this->versionName]++;
                $this->table->insert($data);

                break;

            case 'REJECT':

                /** @var \ArrayObject $penultimate */
                $penultimate = $this->fetchRowVersion($id, 1);
                switch ($penultimate[$this->statusName]) {
                    case 'DRAFT':
                        /** @var \ArrayObject $last */
                        $last = $this->fetchRowVersion($id);
                        $last[$this->statusName] = 'DELETED';
                        $last[$this->identifierName] = $id;
                        $last[$this->versionName]++;
                        $this->table->insert((array)$last);
                        break;
                    case 'READY':
                        /** @var \ArrayObject $last */
                        $last = $this->fetchRowVersion($id);
                        $penultimate[$this->identifierName] = $id;
                        $penultimate[$this->versionName] = ++$last[$this->versionName];
                        $this->table->insert((array)$penultimate);
                        break;
                }

                break;
        }

        /** @var Select $select */
        $select = new Select($this->viewName ?: $this->table->getTable());
        $select->where($this->identifierName, $id);

        /** @var ResultSetInterface $resultSet */
        $resultSet = $this->table->selectWith($select);

        return $resultSet->current();
    }

    /**
     * @param $id
     * @param int $offset
     * @return mixed
     */
    protected function fetchRowVersion($id, $offset = 0)
    {
        /** @var ResultSetInterface $select */
        $resultSet = $this->table->select(function (Select $select) use ($id, $offset) {
            $select->where([$this->identifierName => $id]);
            $select->order($this->versionName . ' DESC');
            $select->limit(1);
            $select->offset($offset);
        });

        return $resultSet->current();
    }

}