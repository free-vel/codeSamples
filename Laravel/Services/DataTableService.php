<?php

namespace App\Services;

class DataTableService
{
    /**
     * @var integer
     */
    public const DATATABLE_TYPES = [
        'appointments' => ['date'],
        'orders' => ['status'],
        'services' => ['category_id', 'name', 'status', 'price', 'duration'],
        'users' => ['first_name', 'last_name', 'status', 'created_at'],
    ];

    public function datatableLimit($query, $request, $total)
    {
        $offset = $request['offset'] ?? 0;
        $limit = $request['limit'] ?? 25;

        return $total ? $query : $query->offset($offset*$limit)->limit($limit);
    }

    public function datatableOrderBy($query, $request, $datatableType)
    {
        if(!isset(self::DATATABLE_TYPES[$datatableType])) return $query;

        $orderBy = isset($request['order_by']) && in_array($request['order_by'], self::DATATABLE_TYPES[$datatableType]) ? $request['order_by'] : self::DATATABLE_TYPES[$datatableType][0];
        $orderDirection = isset($request['order_direction']) && in_array($request['order_direction'], ['asc', 'desc']) ? $request['order_direction'] : 'asc';

        return $query->orderBy($orderBy, $orderDirection);
    }

}
