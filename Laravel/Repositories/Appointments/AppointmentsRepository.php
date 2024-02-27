<?php

namespace App\Repositories\Appointments;

interface AppointmentsRepository
{
    /**
     * Fetches a resource by id.
     *
     * @param  $id
     * @param bool $withRelations
     * @param array $with
     */
    public function byId($id, bool $withRelations = true, array $with = []);

    /**
     * @param array $ids
     * @return mixed
     */
    public function getByIds(array $ids = []): mixed;

    /**
     * @param array $ids
     */
    public function changeStatusToPendingByIds(array $ids = []);

    /**
     * Datatable Request
     *
     * @param  array $request
     * @param boolean $total
     */
    public function datatable(array $request = [], bool $total = false);

    /**
     * @param $id
     * @param $request
     * @return mixed
     */
    public function accept($id, $request): mixed;

    /**
     * @param $id
     * @param $request
     * @return mixed
     */
    public function cancel($id, $request): mixed;


    /**
     * @param $id
     */
    public function start($id);

    /**
     * @param $id
     */
    public function finish($id);

    /**
     * @param $id
     */
    public function pay($id);

    /**
     * @param $id
     * @param $attributes
     */
    public function review($id, $attributes);

    /**
     * Stores a resource.
     *
     * @param  array $attributes
     */
    public function store(array $attributes);

    /**
     * @param array $attributes
     */
    public function create(array $attributes);

    /**
     * @param array $attributes
     * @param $id
     */
    public function edit(array $attributes, $id);

    /**
     * Update a resource.
     *
     * @param  array $attributes
     * @param  $id
     */
    public function update(array $attributes, $id);

    /**
     * Destroy a resource.
     *
     * @param  $id
     */
    public function destroy($id);

    /**
     * @param $attributes
     */
    public function getAvailableScheduleForDate($attributes);

    /**
     * @return array
     */
    public function getTotals(): array;


}
