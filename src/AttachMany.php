<?php

namespace NovaAttachMany;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Authorizable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;

class AttachMany extends Field
{
    use Authorizable;

    public $height = '300px';

    public $fullWidth = false;

    public $showToolbar = true;

    public $showCounts = false;

    public $showOnIndex = false;

    public $component = 'nova-attach-many';

    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;

        $this->fillUsing(function($request, $model, $attribute, $requestAttribute){
            $model->$attribute()->sync(
                json_decode($request->$attribute, true)
            );
            unset($request->$attribute);
        });
    }

    public function rules($rules)
    {
        $rules = ($rules instanceof Rule || is_string($rules)) ? func_get_args() : $rules;

        $this->rules = [ new ArrayRules($rules) ];

        return $this;
    }

    public function resolve($resource, $attribute = null)
    {
        parent::resolve($resource, $attribute);

        $request = resolve(NovaRequest::class);

        $query = $this->resourceClass::newModel();

        $parentResource = $request->findResourceOrFail();

        $resources = $this->resourceClass::relatableQuery($request, $query)->get()
            ->mapInto($this->resourceClass)
            ->filter(function ($resource) use ($request, $parentResource) {
                return $parentResource->authorizedToAttach($request, $resource->resource);
            })
            ->map(function($resource) {
                return [
                    'display' => $resource->title(),
                    'value' => $resource->getKey(),
            ];
        })->values();

        $this->withMeta([
            'height' => $this->height,
            'fullWidth' => $this->fullWidth,
            'showCounts' => $this->showCounts,
            'showToolbar' => $this->showToolbar,
            'value' => $resource->{$this->manyToManyRelationship}->pluck('id')->toArray(),
            'resources' => $resources,
        ]);
    }

    public function authorize(Request $request)
    {
        return call_user_func(
            [$this->resourceClass, 'authorizedToViewAny'], $request
        ) && parent::authorize($request);
    }

    public function height($height)
    {
        $this->height = $height;

        return $this;
    }

    public function fullWidth($fullWidth=true)
    {
        $this->fullWidth = $fullWidth;

        return $this;
    }

    public function hideToolbar()
    {
        $this->showToolbar = false;

        return $this;
    }

    public function showCounts($showCounts=true)
    {
        $this->showCounts = $showCounts;

        return $this;
    }
}
