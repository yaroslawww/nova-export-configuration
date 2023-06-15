<?php


namespace NovaExportConfiguration\Nova\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Nova;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\LaravelNovaExcel\Actions\ExportToExcel;
use Maatwebsite\LaravelNovaExcel\Requests\ExportActionRequest;
use NovaExportConfiguration\Models\ExportStoredFile;

class ExportToExcelAction extends ExportToExcel
{
    protected bool $columnsSelected = false;
    protected array $columns;

    protected ?\Closure $postReplaceFieldValuesWhenOnResource = null;

    public function askForColumns(array $options, string $label = null, callable $callback = null): static
    {
        $this->columns = collect($options)
            ->mapWithKeys(function ($value, $key) {
                if (is_integer($key)) {
                    return [$value => Nova::humanize(Str::camel($value))];
                }

                return [$key => $value];
            })
            ->all();

        $field = BooleanGroup::make(
            __($label ?: 'Columns')
        )
            ->options($this->columns)
            ->default(function () {
                return array_combine(
                    array_keys($this->columns),
                    array_fill(0, count($this->columns), true)
                );
            });

        if (is_callable($callback)) {
            $callback($field);
        }

        $this->actionFields[] = $field;

        return $this;
    }

    public function handleRequest(ActionRequest $request)
    {
        $this->handleColumns($request);

        return parent::handleRequest($request);
    }

    public function handle(ActionRequest $request, Action $exportable): mixed
    {
        $dbExport = ExportStoredFile::init(
            $request->resource()::uriKey(),
            $this->getDisk() ?: config('nova-export-configuration.defaults.disk_export_action'),
            date('Y/m/d/') . Str::uuid() . '.' . $this->getDefaultExtension(),
            $this->getFilename(),
            function ($file) use ($request) {
                $file->meta
                    ->setAttribute('fields', $request->resolveFields())
                    ->setAttribute('filters', $request->filters);
                if ($user = $request->user()) {
                    $file->meta->toMorph('author', $user);
                }
            }
        );

        $response = Excel::store(
            $exportable,
            $dbExport->path,
            $dbExport->disk,
            $this->getWriterType()
        );

        if (false === $response) {
            return \is_callable($this->onFailure)
                ? ($this->onFailure)($request, $response)
                : Action::danger(__('Resource could not be exported.'));
        }

        $dbExport->save();

        return \is_callable($this->onSuccess)
            ? ($this->onSuccess)($request, $response)
            : Action::message(__('Data exported to file.'));
    }

    protected function handleColumns(ActionRequest $request): void
    {
        $fields = $request->resolveFields();

        if ($columns = $fields->get('columns')) {
            $activeColumns = array_keys(array_filter($columns));
            if (count($activeColumns) > 0) {
                $this->columnsSelected = true;
                $this->only            = $activeColumns;
            }
        }
    }

    protected function handleHeadings($query, ExportActionRequest $request)
    {
        if ($this->columnsSelected) {
            $this->headings = Arr::only($this->columns, $this->only);
        } else {
            $this->headings = collect($this->only)
                ->map(function ($item) {
                    return Str::title($item);
                })
                ->toArray();
        }
    }

    protected function isExportableField(Field $field): bool
    {
        if ($field->attribute == 'email') {
            return false;
        }

        return parent::isExportableField($field);
    }

    public function setPostReplaceFieldValuesWhenOnResource(?\Closure $closure): static
    {
        $this->postReplaceFieldValuesWhenOnResource = $closure;

        return $this;
    }


    protected function replaceFieldValuesWhenOnResource(Model $model, array $only = []): array
    {
        $row = parent::replaceFieldValuesWhenOnResource($model, $only);

        if (is_callable($this->postReplaceFieldValuesWhenOnResource)) {
            $row = call_user_func_array(
                $this->postReplaceFieldValuesWhenOnResource,
                [$row, $model, $only,]
            );
        }

        return $row;
    }
}
