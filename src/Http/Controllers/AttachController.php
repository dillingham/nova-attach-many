<?php

namespace NovaAttachMany\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class AttachController extends Controller
{
    public function create(NovaRequest $request, $parent, $relationship)
    {
        return [
            'available' => $this->getAvailableResources($request, $relationship),
        ];
    }

    public function edit(NovaRequest $request, $parent, $parentId, $relationship)
    {
        // lookup the resource
        $foundResource = $request->findResourceOrFail();

        // if the $relationship() method exists, we will use that to
        // determine the keyName
        if(method_exists($foundResource->model(), $relationship)){
            $keyName = $foundResource->model()->{$relationship}()->getModel()->getKeyName();
            // otherwise default to an assumed keyName of `id`
        }else{
            $keyName = 'id';
        }

        return [
            'selected' => $foundResource->model()->{$relationship}->pluck($keyName),
            'available' => $this->getAvailableResources($request, $relationship),
        ];
    }

    public function getAvailableResources($request, $relationship)
    {
        $resourceClass = $request->newResource();

        $field = $resourceClass
            ->availableFields($request)
            ->where('component', 'nova-attach-many')
            ->where('attribute', $relationship)
            ->first();

        $query = $field->resourceClass::newModel();

        return forward_static_call($this->associatableQueryCallable($request, $query), $request, $query)->get()
            ->mapInto($field->resourceClass)
            ->filter(function ($resource) use ($request, $field) {
                return $request->newResource()->authorizedToAttach($request, $resource->resource);
            })->map(function ($resource) use ($request, $field) {
                return $field->formatAssociatableResource($request, $resource);
            })->sortBy('display')->values();
    }
    
    /**
     * Get the associatable query method name.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    protected function associatableQueryCallable(NovaRequest $request, $model)
    {
        return ($method = $this->associatableQueryMethod($request, $model))
            ? [$request->resource(), $method]
            : [$request->newResource(), 'relatableQuery'];
    }

    /**
     * Get the associatable query method name.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    protected function associatableQueryMethod(NovaRequest $request, $model)
    {
        $method = 'relatable'.Str::plural(class_basename($model));

        if (method_exists($request->resource(), $method)) {
            return $method;
        }
    }
}
