<?php

namespace NovaAttachMany;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Nova\Authorizable;
use Laravel\Nova\Fields\Field;
use NovaAttachMany\Rules\ArrayRules;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Fields\FormatsRelatableDisplayValues;

class AttachMany extends Field
{
    use Authorizable;

    use FormatsRelatableDisplayValues;

    public $height = '300px';

    public $fullWidth = false;

    public $showToolbar = true;

    public $showCounts = false;

    public $showPreview = false;

    public $showRefresh = false;

    public $showSubtitle = false;

    public $showOnIndex = false;

    public $showOnDetail = false;

    public $display;

    public $component = 'nova-attach-many';

    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resource = $resource;

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;

        $this->fillUsing(function($request, $model, $attribute, $requestAttribute) use($resource) {
            if(is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
                $model::saved(function($model) use($attribute, $request) {

                    // fetch the submitted values
                    $values = json_decode(request()->input($attribute), true);
                    
                    // if $values is null make it an empty array instead
                    if (is_null($values)) {
                        $values = [];
                    }

                    // remove `null` values that may be submitted
                    $filtered_values = array_filter($values);

                    // sync
                    $changes = $model->$attribute()->sync($filtered_values);

                    $method = Str::camel($attribute) . 'Synced';

                    $parent = $request->newResource();

                    if (method_exists($parent, $method)) {
                        $parent->{$method}($changes);
                    }
                });

                // prevent relationship json on parent resource:

                $request->replace(
                    $request->except($attribute)
                );
            }
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
        $this->withMeta([
            'height' => $this->height,
            'fullWidth' => $this->fullWidth,
            'showCounts' => $this->showCounts,
            'showPreview' => $this->showPreview,
            'showToolbar' => $this->showToolbar,
            'showRefresh' => $this->showRefresh,
            'showSubtitle' => $this->showSubtitle,
        ]);
    }

    public function authorize(Request $request)
    {
        if (property_exists($this, 'seeCallback') && ! is_null($this->seeCallback)) {
            return $this->authorizedToSee($request);
        }

        if(! $this->resourceClass::authorizable()) {
            return true;
        }

        if(! isset($request->resource)) {
            return false;
        }

        return call_user_func([ $this->resourceClass, 'authorizedToViewAny'], $request)
            && $request->newResource()->authorizedToAttachAny($request, $this->resourceClass::newModel())
            && parent::authorize($request);
    }

    public function formatAssociatableResource(NovaRequest $request, $resource)
    {
        $item = [
            'display' => $this->formatDisplayValue($resource),
            'value' => $resource->getKey(),
        ];

        if($this->showSubtitle) {
            $item['subtitle'] = $resource->subtitle();
        }

        return $item;
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

    public function showPreview($showPreview=true)
    {
        $this->showPreview = $showPreview;

        return $this;
    }

    public function showRefresh($showRefresh=true)
    {
        $this->showRefresh = $showRefresh;

        return $this;
    }

    public function showSubtitle($showSubtitle=true)
    {
        $this->showSubtitle = $showSubtitle;

        return $this;
    }
}
