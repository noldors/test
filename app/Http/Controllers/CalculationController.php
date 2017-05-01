<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalculation;
use App\Http\Requests\UpdateCalculation;
use App\Models\Calculation;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CalculationController extends Controller
{
    /**
     * @var \Illuminate\Contracts\Auth\Factory
     */
    private $guard;

    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $view;

    /**
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    private $urlGenerator;

    /**
     * CalculationController constructor.
     *
     * @param \Illuminate\Contracts\Auth\Guard           $guard
     * @param \Illuminate\Contracts\View\Factory         $view
     * @param \Illuminate\Contracts\Routing\UrlGenerator $urlGenerator
     */
    public function __construct(Guard $guard, ViewFactory $view, UrlGenerator $urlGenerator)
    {
        $this->guard = $guard;
        $this->view = $view;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $user = $this->guard->user();
        $calculations = (new Calculation())->getList($user);

        return $this->view->make('calculations.list', compact('calculations', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return $this->view->make('calculations.form', ['method' => 'POST', 'action' => $this->urlGenerator->route('calculations.store')]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCalculation $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCalculation $request)
    {
        (new Calculation([
            'name'    => $request->get('name'),
            'source'  => $request->get('source'),
            'user_id' => $this->guard->id()
        ]))->save();

        // If we receive code 200 it means that entity was saved to database.
        return new JsonResponse(null, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  Calculation $calculation
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Calculation $calculation)
    {
        $this->authorize('view', $calculation);

        return $this->view->make('calculations.show', compact('calculation'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Calculation $calculation
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Calculation $calculation)
    {
        $this->authorize('update', $calculation);

        return $this->view->make('calculations.form',
            [
                'calculation'   => $calculation,
                'method'        => 'PUT',
                'action'        => $this->urlGenerator->route('calculations.update', [$calculation]),
                'disableSource' => true
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCalculation $request
     * @param  Calculation       $calculation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCalculation $request, Calculation $calculation)
    {
        $this->authorize('update', $calculation);

        $calculation->update(
            [
                'name' => $request->get('name'),
            ]
        );

        // If we receive code 200 it means that entity was updated.
        return new JsonResponse(null, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Calculation $calculation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Calculation $calculation)
    {
        $this->authorize('delete', $calculation);

        $calculation->delete();

        // If we receive code 200 it means that entity was deleted.
        return new JsonResponse(null, 200);
    }
}
