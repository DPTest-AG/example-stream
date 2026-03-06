<?php

const ORI = ["Desktop", "Landscape", "Portrait"];
const MAP_KEYS = [
    'slot' => 'Slot',
    'web_total_count' => 'Web Total',
    'web_completed_count' => 'Completed',
    'web_outstanding_count' => 'Outstanding',
    'mobi_total_count' => 'Mobile Total',
    'mobi_completed_count' => 'Completed',
    'mobi_outstanding_count' => 'Outstanding',
    'both_total_count' => 'Both Versions Total',
    'both_completed_count' => 'Completed',
    'both_outstanding_count' => 'Outstanding',
];
class SlotTotal {
    public $slot;
    public $web_total_count = 0;
    public $web_completed_count = 0;
    public $web_outstanding_count = 0;
    public $mobi_total_count = 0;
    public $mobi_completed_count = 0;
    public $mobi_outstanding_count = 0;
    public $both_total_count = 0;
    public $both_completed_count = 0;
    public $both_outstanding_count = 0;
}

class SlotByDate {
    public $slot;
    public $web_completed_count = 0;
    public $mobi_completed_count = 0;
    public $both_completed_count = 0;
}

class Bug {
    public string $slot;
    public ?string $approveOwn;
    public ?string $approveDev;
    public ?string $priority;
    public ?string $category;
    public ?string $orientation;
    public ?string $errorDescription;
    public ?string $errorFinder;
    public ?string $errorProof;
    public ?string $assignee;
    public ?string $statusArt;
    public ?string $statusAnim;
    public ?string $statusSound;
    public ?string $statusFront;
    public ?string $status;
    public ?string $resolveDate;
    private array $all_orientations;

    public function __construct(array $row)
    {
        $this->slot            = $row[0]  ?? '';
        $this->approveOwn      = $row[1]  ?? null;
        $this->approveDev      = $row[2]  ?? null;
        $this->priority        = $row[3]  ?? null;
        $this->category        = $row[4]  ?? null;
        $this->orientation     = $row[5]  ?? null;
        $this->errorDescription= $row[6]  ?? null;
        $this->errorFinder     = $row[7]  ?? null;
        $this->errorProof      = $row[8]  ?? null;
        $this->assignee        = $row[9]  ?? null;
        $this->statusArt       = $row[10] ?? null;
        $this->statusAnim      = $row[11] ?? null;
        $this->statusSound     = $row[12] ?? null;
        $this->statusFront     = $row[13] ?? null;
        $this->status          = $row[14] ?? null;
        $this->resolveDate     = $row[15] ?? null;

        $all_orientations = [];
        $orientations = explode(', ', $this->orientation);
        foreach ($orientations as $orientation) {
            $orientation_chunk = explode('/', $orientation);
            foreach ($orientation_chunk as $item) {
                $all_orientations[] = $item;
            }
        }
        $this->all_orientations = array_filter($all_orientations);
    }

    public function getResolveDate() {
        $chunks = array_filter($dates = preg_split('/\r\n|\r|\n/', $this->resolveDate));

        if (empty($chunks)) {
            return "";
        }

        if (count($chunks) < 2) {
            $rs = $this->resolveDate;
        } else {
            $rs = max($chunks);
        }

        try {
            $dt = new DateTime($rs);
            return $dt->format("Y-m-d");
        } catch (Exception $e) {
            print_r($this);
            echo $e->getMessage();
            return "";
        }
    }
    public function isBroken() {
        return $this->slot == "";
    }

    public function isBothVersion(): bool
    {
        $is_all = true;

        if (empty($this->all_orientations)) {
            return false;
        }

        foreach (ORI as $check_ori) {
            if (!in_array($check_ori, $this->all_orientations)) {
                $is_all = false;
            }
        }

        return $is_all;
    }

    public function isFinished(): bool
    {
        return $this->status == 'Done';
    }
}

class Analyzer {
    /**
     * @var array
     */
    protected array $mobi_csv;

    /**
     * @var array []Bug
     */
    protected array $web_csv;

    protected $totals = [];
    protected $by_dates = [];

    public function __construct($web_filename, $mobi_filename) {
        $this->mobi_csv = $this->parseCsv($mobi_filename);
        $this->web_csv = $this->parseCsv($web_filename);
        $this->totals = [];
        $this->by_dates = [];
    }

    public function loadFile($filename) {
        return file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    public function outputByDates($filename, $records) {
        $fp = fopen($filename, 'w');


        fputcsv($fp, [
            "Slot",  "Web Completed", "Mobile Completed", "Both Completed"
        ]);

        ksort($records);

        foreach ($records as $date => $slots) {
            if (empty($date)) {
                continue;
            }

            list($web_totals, $mobi_totals, $both_totals) = $this->collectTotalsBySlots($slots);
            fputcsv($fp, [$date, $web_totals, $mobi_totals, $both_totals]);

            foreach ($slots as $slot => $totals) {
                fputcsv($fp, [$slot, $totals->web_completed_count, $totals->mobi_completed_count, $totals->both_completed_count]);
            }
            fputcsv($fp, []);
            fputcsv($fp, []);
        }

        fclose($fp);

        echo "CSV saved: $filename\n";

    }

    private function collectTotalsBySlots($slots) {
        $web_totals = 0; $mobi_totals = 0; $both_totals = 0;

        foreach ($slots as $slot) {
            $web_totals += $slot->web_completed_count;
            $mobi_totals += $slot->mobi_completed_count;
            $both_totals += $slot->both_completed_count;
        }

        return [$web_totals, $mobi_totals, $both_totals];
    }

    public function outputTotals($filename, $records)
    {
        $fp = fopen($filename, 'w');

        $reset_record = (array)reset($records);
        $headers = [];

        foreach (array_keys($reset_record) as $item) {
            $headers[] = MAP_KEYS[$item] ?? $item;
        }
        fputcsv($fp, $headers);

        foreach ($records as $record) {
            $result = [];

            foreach (array_keys($reset_record) as $array_key) {
                $result[] = $record->{$array_key};
            }
            fputcsv($fp, $result);
        }

        fclose($fp);

        echo "CSV saved: $filename\n";
    }


    /**
     * Парсит CSV-файл или строку CSV в массив.
     *
     * @param string $input Путь к CSV-файлу ИЛИ CSV-строка
     * @param string $delimiter Разделитель (по умолчанию запятая)
     * @param string $enclosure Символ обрамления (по умолчанию ")
     * @return array
     */
    protected function parseCsv($input, $delimiter = ',', $enclosure = '"'): array
    {
        $rows = [];

        $handle = fopen($input, 'r');
        if (!$handle) {
            throw new Exception("Не удалось открыть файл: $input");
        }

        while (($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false) {
            // Пропускаем полностью пустые строки
            if ($data === [null] || empty(array_filter($data, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $rows[] = new Bug($data);
        }

        fclose($handle);

        return $rows;
    }

    public function analyzeTotals()
    {
        $this->analyzeTotalsBySource($this->web_csv, "web_total_count", "web_completed_count", "web_outstanding_count");
        $this->analyzeTotalsBySource($this->mobi_csv, "mobi_total_count", "mobi_completed_count", "mobi_outstanding_count");

        $this->filterByDates();
        return [$this->totals, $this->by_dates];
    }


    /**
     * @param $rows []Bug
     * @param $key
     * @return array
     */
    private function groupByKey($rows, $key) {
        $result = [];

        foreach ($rows as $index => $row) {
            $result[$row->{$key}][$index] = $row;
        }

        return $result;
    }

    private function isBrokenSlot($key)
    {
        $key = trim($key);
        if ($key == 'Slot') {
            return true;
        }

        if (strlen($key) < 5) {
            return true;
        }

        return $key == "";
    }

    private function analyzeTotalsBySource($source, $total_key, $completed_key, $out_key)
    {
        $bugs_list = $this->groupByKey($source, "slot");

        foreach ($bugs_list as $key => $bugs) {
            $key = trim($key);

            if ($this->isBrokenSlot($key)) {
                continue;
            }

            if (@!$this->totals[$key]) {
                $this->totals[$key] = new SlotTotal();
                $this->totals[$key]->slot = $key;
            }

            $total = $this->totals[$key];


            foreach ($bugs as $bug) {
                /** @var Bug $bug */

                if (@!$this->by_dates[$bug->getResolveDate()][$key]) {
                    $this->by_dates[$bug->getResolveDate()][$key] = new SlotByDate();
                    $this->by_dates[$bug->getResolveDate()][$key]->slot = $key;
                }

                $total_by_date = $this->by_dates[$bug->getResolveDate()][$key];

                if ($bug->isBothVersion()) {
                    $total->both_total_count += 1;

                    if ($bug->isFinished()) {
                        $total->both_completed_count += 1;
                        $total_by_date->both_completed_count += 1;
                    }
                } else {
                    $total->{$total_key} += 1;

                    if ($bug->isFinished()) {
                        $total->{$completed_key} += 1;
                        $total_by_date->{$completed_key} += 1;
                    }
                }
            }

            $total->{$out_key} = $total->{$total_key} - $total->{$completed_key};
            $total->both_outstanding_count = $total->both_total_count - $total->both_completed_count;
        }
    }

    private function filterByDates()
    {
        foreach ($this->by_dates as $slot => $resolved_by_date) {
            foreach ($resolved_by_date as $key => $totals) {
                if ($key == "") {
                    unset($this->by_dates[$slot][$key]);
                }
            }
        }

        $this->by_dates = array_filter($this->by_dates);
    }

}

$a = new Analyzer("./input/web.csv", "./input/mobi.csv");
list ($totals, $by_dates) = $a->analyzeTotals();
$a->outputTotals("./output/totals.csv", $totals);
$a->outputByDates("./output/by_dates.csv", $by_dates);
