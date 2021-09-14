<?php



namespace com\cminds\payperposts\helper;



class CSVHelper {

	

	protected $path;

	protected $fp;

	

	function __construct($path) {

		$this->path = $path;

	}

	

	static function downloadCSV(array $data, $filename) {
		
		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='. $filename .'.csv');
		
		$output = fopen('php://output', 'w');
		
		foreach ($data as $row) {
			fputcsv($output, $row);
		}
		
		fclose($output);
		exit;
		
	}
	

	function getAll($colNumber = null) {

		$rows = array();

		$this->fp = fopen($this->getPath(), 'r');

		$counter = 0;
		while ($row = fgetcsv($this->fp)) {

			if (!empty($row) && $counter > 0) {

				if (is_null($colNumber)) {

					$rows[] = $row;

				}

				else if (isset($row[$colNumber])) {

					$rows[] = $row[$colNumber];

				}

			}

			$counter++;
		}

		fclose($this->fp);

		return $rows;

	}

	

	

	function getPath() {

		return $this->path;

	}

	

}