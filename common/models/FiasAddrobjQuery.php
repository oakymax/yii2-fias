<?php

namespace ejen\fias\common\models;

use yii\db\ActiveQuery;

/**
 * Конструктор запросов к таблице адресообразующих элементов
 */
class FiasAddrobjQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return array|FiasAddrobj[]
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return array|null|FiasAddrobj
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Только актуальные записи (не исторические, и не копии)
     * Проверка
     * - по полю currstatus == 0 (статус актуальности КЛАДР4)
     * @param string|null $alias
     * @return $this
     */
    public function actual($alias = null)
    {
        $alias = ($alias ? "{$alias}." : "");

        $this->andWhere([$alias . "actual" => true]);

        return $this;
    }

    /**
     * Получить только последне адреса в исторической цепочке
     * @param string $alias
     * @return $this
     */
    public function last($alias = null)
    {
        $alias = ($alias ? "{$alias}." : "");

        $this->andWhere("({$alias}currstatus = 0 OR {$alias}nextid IS NULL OR {$alias}nextid = '')");

        return $this;
    }

    /**
     * Выбрать только записи не помеченные как "копии" (в приоритете записи добавленные в ГИС)
     * @param string $alias
     * @return $this
     */
    public function validForGisgkh($alias = null)
    {
        $alias = empty($alias) ? '' : $alias . '.';

        $this->andWhere("({$alias}fias_addrobjguid IS NULL OR {$alias}fias_addrobjguid = '')");

        return $this;
    }

    /**
     * Поиск по GUID-у
     * @param string $aoguid глобально уникальный идентификатор
     * @param string|null $alias
     * @return $this
     */
    public function byGuid($aoguid, $alias = null)
    {
        $alias = ($alias ? "{$alias}." : "");
        $this->andWhere([
            $alias . "aoguid" => $aoguid
        ]);
        return $this;
    }

    /**
     * Поиск по GUID-у родительского элемента
     * @param string $aoguid глобально уникальный идентификатор
     * @param string|null $alias
     * @return $this
     */
    public function byParentGuid($aoguid, $alias = null)
    {
        $alias = ($alias ? "{$alias}." : "");
        $this->andWhere([
            $alias . "parentguid" => $aoguid
        ]);
        return $this;
    }

    /**
     * Поиск по официальному наименованию
     * @param string $q строка запроса
     * @param string|null $alias
     * @return $this
     */
    public function byFormalName($q, $alias = null)
    {
        $alias = ($alias ? "{$alias}." : "");
        $this->andWhere([
            'like', "upper({$alias}formalname COLLATE \"ru_RU\")", mb_strtoupper($q)
        ]);
        return $this;
    }

    /**
     * Поиск по полному названию
     * @param string $q строка запроса
     * @param string|null $alias
     * @return $this
     */
    public function byFullName($q, $alias = null)
    {
        $alias = $alias ?: FiasAddrobj::tableName();

        $parts = mb_split('[\s,.]+', $q);

        foreach ($parts as $part) {
            $part = str_replace("Ё", "Е", mb_strtoupper($part));
            $this->andWhere([
                'like', "{$alias}.fulltext_search_upper", $part
            ]);
        }

        return $this;
    }

    /**
     * Поиск по полному названию, каждый элемент ищется с полным вхождением
     * @param string $q строка запроса
     * @param string|null $alias
     * @return $this
     */
    public function byFullNameStrictParts($q, $alias = null)
    {
        $alias = $alias ?: FiasAddrobj::tableName();

        $parts = mb_split('[\s,.]+', $q);
        $parts = array_filter($parts);

        foreach ($parts as $part) {
            $part = str_replace("Ё", "Е", mb_strtoupper($part));
            $this->andWhere([
                'or',
                ['like', "{$alias}.fulltext_search_upper", $part . " %", false],
                ['like', "{$alias}.fulltext_search_upper", "% " . $part . " %", false],
                ['like', "{$alias}.fulltext_search_upper", "% " . $part, false],
                ['like', "{$alias}.fulltext_search_upper", "% " . $part . ".%", false],
                ['like', "{$alias}.fulltext_search_upper", "%." . $part . " %", false],
                ['like', "{$alias}.fulltext_search_upper", "%." . $part . ".%", false],
                ['like', "{$alias}.fulltext_search_upper", "% " . $part . ",%", false],
            ]);
        }

        return $this;
    }

    /**
     * Выбрать адресообразующие элементы заданного уровня
     * @param string $aolevel уровень адресообразующего элемента (см. константы `FiasAddrobj::AOLEVEL_*`)
     * @param string|null $alias
     * @return $this
     */
    public function byLevel($aolevel, $alias = null)
    {
        $alias = ($alias ? "{$alias}." : "");
        $this->andWhere([
            $alias . "aolevel" => $aolevel
        ]);
        return $this;
    }

    /**
     * Ограничить выборку заданным регионом (по коду региона)
     * @param integer $regionCode
     * @param string|null $alias
     * @return $this
     */
    public function byRegionCode($regionCode, $alias = null)
    {
        $alias = ($alias ? "{$alias}." : "");
        $this->andWhere([
            $alias . "regioncode" => $regionCode
        ]);
        return $this;
    }

    /**
     * Выбрать только те элементы у которых есть подчинённые записи об адресных объекта
     * @param string|null $alias
     * @return $this
     */
    public function withChildHouses($alias = null)
    {
        $alias = ($alias ? "{$alias}." : "");
        $this->andWhere([
            '>', $alias . "houses_count", 0
        ]);
        return $this;
    }

    /**
     * Сортировать по названию
     * @param string|null $alias
     * @return $this
     */
    public function orderByName($alias = null)
    {
        $alias = ($alias ? "{$alias}." : "");
        $this->orderBy([$alias . 'formalname' => SORT_ASC]);
        return $this;
    }
}