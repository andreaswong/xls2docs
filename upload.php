<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as XLSIOFactory;
use Ramsey\Uuid\Uuid;

function handleUpload($xlsPath) {	
	$boldFont = new Font();
	$boldFont->setBold(true);

	$masterWord = new PhpWord();
	$masterWriter = IOFactory::createWriter($masterWord, 'Word2007');

	$reader = XLSIOFactory::createReaderForFile($xlsPath);
	$reader->setReadDataOnly(true);
	$spreadsheet = $reader->load($xlsPath);
	$worksheet = $spreadsheet->getActiveSheet();

	$fields = [];
	$outputFolder = 'outputs/'.Uuid::uuid4();

	if(!is_dir($outputFolder)) {
		if(!mkdir($outputFolder) && !is_dir($outputFolder)) {
			return;
		}
	}

	foreach($worksheet->getRowIterator() as $i => $row) {
		$cellIterator = $row->getCellIterator();
		$withValues = 0;

		$phpWord = new PhpWord();
		$section = $phpWord->addSection();
		$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
		$textrun = $section->addTextRun();

		$masterSection = $masterWord->addSection();
		$masterTextRun = $masterSection->addTextRun();
		$filename = $i;
		foreach($cellIterator as $j => $cell) {
			if($i == 1) {
				$fields[$j] = $cell->getValue();
				continue;
			}

			$cellValue = $cell->getValue();
			if($cellValue != '') {
				$withValues++;

				$textrun->addText($fields[$j] . ' ', $boldFont);
				$textrun->addText($cell->getValue());
				$textrun->addTextBreak(2);

				$masterTextRun->addText($fields[$j] . ' ', $boldFont);
				$masterTextRun->addText($cell->getValue());
				$masterTextRun->addTextBreak(2);
			}

			if($j == 'B') {
				$filename = $cell->getValue();
			}

		}

		if($i == 1) continue;

		if($withValues > 0) {
			$masterSection->addPageBreak();
			$objWriter->save($outputFolder.'/'.$filename.'.docx');
		}
	}

	$masterWriter->save($outputFolder .'/master.docx');

	$zipArchive = new ZipArchive();
	$zipFile = $outputFolder . '/archive.zip';

	$err = $zipArchive->open($outputFolder . '/archive.zip', ZIPARCHIVE::OVERWRITE | ZIPARCHIVE::CREATE);

	$zipArchive->addGlob($outputFolder . "/*.docx", GLOB_BRACE, ['remove_all_path' => true]);
	if (!$zipArchive->status == ZIPARCHIVE::ER_OK)
	    die("Failed to write files to zip");

	$zipArchive->close();
	return $outputFolder . '/archive.zip';
}
