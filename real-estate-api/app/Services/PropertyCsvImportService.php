<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use SplFileObject;

class PropertyCsvImportService
{
    protected const COLUMNS = ['title', 'location', 'price', 'type', 'image', 'description'];

    protected const MAX_ROWS = 500;

    /**
     * @return array{imported: int, skipped: array<int, array{row: int, errors: array<int, string>}>}
     */
    public function import(UploadedFile $file): array
    {
        $rows = $this->readRows($file);
        $imported = 0;
        $skipped = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, [
                'title' => ['required', 'string', 'max:255'],
                'location' => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0'],
                'type' => ['required', 'string', 'in:House,Apartment,Villa,Land,Office'],
                'image' => ['nullable', 'url:http,https', 'max:2048'],
                'description' => ['required', 'string', 'max:5000'],
            ]);

            if ($validator->fails()) {
                $skipped[] = [
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                ];

                continue;
            }

            Property::create($validator->validated());
            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function readRows(UploadedFile $file): array
    {
        $csv = new SplFileObject($file->getRealPath());
        $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD);

        $rows = [];
        $header = null;

        foreach ($csv as $line) {
            if (! is_array($line) || $line === [null]) {
                continue;
            }

            if ($header === null) {
                $header = array_map(fn ($column) => strtolower(trim((string) $column)), $line);

                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                break;
            }

            $values = array_map(fn ($value) => $value === null || trim((string) $value) === '' ? null : trim((string) $value), $line);
            $row = [];

            foreach (self::COLUMNS as $column) {
                $position = array_search($column, $header, true);
                $row[$column] = $position === false ? null : ($values[$position] ?? null);
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
