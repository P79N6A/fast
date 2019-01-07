<?php
 
include_once('BarcodeBase.php');

define('CODE128_A',	1);			// Table A
define('CODE128_B',	2);			// Table B
define('CODE128_C',	3);			// Table C

class Code128 extends Barcode1D {
	const KEYA_FNC3 = 96;
	const KEYA_FNC2 = 97;
	const KEYA_SHIFT = 98;
	const KEYA_CODEC = 99;
	const KEYA_CODEB = 100;
	const KEYA_FNC4 = 101;
	const KEYA_FNC1 = 102;

	const KEYB_FNC3 = 96;
	const KEYB_FNC2 = 97;
	const KEYB_SHIFT = 98;
	const KEYB_CODEC = 99;
	const KEYB_FNC4 = 100;
	const KEYB_CODEA = 101;
	const KEYB_FNC1 = 102;

	const KEYC_CODEB = 100;
	const KEYC_CODEA = 101;
	const KEYC_FNC1 = 102;

	const KEY_STARTA = 103;
	const KEY_STARTB = 104;
	const KEY_STARTC = 105;

	const KEY_STOP = 106;

	protected $keysA, $keysB, $keysC;
	private $starting_text;
	private $indcheck, $data;
	private $tilde;

	private $shift;
	private $latch;
	private $fnc;

	private $METHOD			= NULL;  
 
	public function __construct($start = NULL) {
		parent::__construct();

		/* CODE 128 A */
		$this->keysA = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_';
		for ($i = 0; $i < 32; $i++) {
			$this->keysA .= chr($i);
		}

		/* CODE 128 B */
		$this->keysB = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~'.chr(127);

		/* CODE 128 C */
		$this->keysC = '0123456789';

		$this->code = array(
			'101111',	/* 00 */
			'111011',	/* 01 */
			'111110',	/* 02 */
			'010112',	/* 03 */
			'010211',	/* 04 */
			'020111',	/* 05 */
			'011102',	/* 06 */
			'011201',	/* 07 */
			'021101',	/* 08 */
			'110102',	/* 09 */
			'110201',	/* 10 */
			'120101',	/* 11 */
			'001121',	/* 12 */
			'011021',	/* 13 */
			'011120',	/* 14 */
			'002111',	/* 15 */
			'012011',	/* 16 */
			'012110',	/* 17 */
			'112100',	/* 18 */
			'110021',	/* 19 */
			'110120',	/* 20 */
			'102101',	/* 21 */
			'112001',	/* 22 */
			'201020',	/* 23 */
			'200111',	/* 24 */
			'210011',	/* 25 */
			'210110',	/* 26 */
			'201101',	/* 27 */
			'211001',	/* 28 */
			'211100',	/* 29 */
			'101012',	/* 30 */
			'101210',	/* 31 */
			'121010',	/* 32 */
			'000212',	/* 33 */
			'020012',	/* 34 */
			'020210',	/* 35 */
			'001202',	/* 36 */
			'021002',	/* 37 */
			'021200',	/* 38 */
			'100202',	/* 39 */
			'120002',	/* 40 */
			'120200',	/* 41 */
			'001022',	/* 42 */
			'001220',	/* 43 */
			'021020',	/* 44 */
			'002012',	/* 45 */
			'002210',	/* 46 */
			'022010',	/* 47 */
			'202010',	/* 48 */
			'100220',	/* 49 */
			'120020',	/* 50 */
			'102002',	/* 51 */
			'102200',	/* 52 */
			'102020',	/* 53 */
			'200012',	/* 54 */
			'200210',	/* 55 */
			'220010',	/* 56 */
			'201002',	/* 57 */
			'201200',	/* 58 */
			'221000',	/* 59 */
			'203000',	/* 60 */
			'110300',	/* 61 */
			'320000',	/* 62 */
			'000113',	/* 63 */
			'000311',	/* 64 */
			'010013',	/* 65 */
			'010310',	/* 66 */
			'030011',	/* 67 */
			'030110',	/* 68 */
			'001103',	/* 69 */
			'001301',	/* 70 */
			'011003',	/* 71 */
			'011300',	/* 72 */
			'031001',	/* 73 */
			'031100',	/* 74 */
			'130100',	/* 75 */
			'110003',	/* 76 */
			'302000',	/* 77 */
			'130001',	/* 78 */
			'023000',	/* 79 */
			'000131',	/* 80 */
			'010031',	/* 81 */
			'010130',	/* 82 */
			'003101',	/* 83 */
			'013001',	/* 84 */
			'013100',	/* 85 */
			'300101',	/* 86 */
			'310001',	/* 87 */
			'310100',	/* 88 */
			'101030',	/* 89 */
			'103010',	/* 90 */
			'301010',	/* 91 */
			'000032',	/* 92 */
			'000230',	/* 93 */
			'020030',	/* 94 */
			'003002',	/* 95 */
			'003200',	/* 96 */
			'300002',	/* 97 */
			'300200',	/* 98 */
			'002030',	/* 99 */
			'003020',	/* 100*/
			'200030',	/* 101*/
			'300020',	/* 102*/
			'100301',	/* 103*/
			'100103',	/* 104*/
			'100121',	/* 105*/
			'122000'	/*STOP*/
		);
		$this->setStart($start);
		$this->setTilde(true);

		$this->latch = array(
			array(null,				self::KEYA_CODEB,	self::KEYA_CODEC),
			array(self::KEYB_CODEA,		null,				self::KEYB_CODEC),
			array(self::KEYC_CODEA,		self::KEYC_CODEB,	null)
		);
		$this->shift = array(
			array(null,				self::KEYA_SHIFT),
			array(self::KEYB_SHIFT,		null)
		);
		$this->fnc = array(
			array(self::KEYA_FNC1,		self::KEYA_FNC2,	self::KEYA_FNC3,	self::KEYA_FNC4),
			array(self::KEYB_FNC1,		self::KEYB_FNC2,	self::KEYB_FNC3,	self::KEYB_FNC4),
			array(self::KEYC_FNC1,		null,				null,				null)
		);

		// Method available
		$this->METHOD		= array(CODE128_A => 'A', CODE128_B => 'B', CODE128_C => 'C');
	}

 
	public function setStart($table) {
		if ($table !== 'A' && $table !== 'B' && $table !== 'C' && $table !== NULL) {
			throw new BarcodeException('The starting table must be A, B, C or NULL.table');
		}

		$this->starting_text = $table;
	}

 
	public function getTilde() {
		return $this->tilde;
	}

 
	public function setTilde($accept) {
		$this->tilde = (bool)$accept;
	}
 
	public function parse($text) {
		$this->setStartFromText($text);

		$this->text = '';
		$seq = '';

		$currentMode = $this->starting_text;

		// Here, we format correctly what the user gives.
		if (!is_array($text)) {
			$seq = $this->getSequence($text, $currentMode);
			$this->text = $text;
		} else {
			// This loop checks for UnknownText AND raises an exception if a character is not allowed in a table
			reset($text);
			while (list($key1, $val1) = each($text)) {			// We take each value
				if (!is_array($val1)) {					// This is not a table
					if (is_string($val1)) {				// If it's a string, parse as unknown
						$seq .= $this->getSequence($val1, $currentMode);
						$this->text .= $val1;
					} else {
						// it's the case of "array(ENCODING, 'text')"
						// We got ENCODING in $val1, calling 'each' again will get 'text' in $val2
						list($key2, $val2) = each($text);
						$seq .= $this->{'setParse' . $this->METHOD[$val1]}($val2, $currentMode);
						$this->text .= $val2;
					}
				} else {						// The method is specified
					// $val1[0] = ENCODING
					// $val1[1] = 'text'
					$value = isset($val1[1]) ? $val1[1] : '';	// If data available
					$seq .= $this->{'setParse' . $this->METHOD[$val1[0]]}($value, $currentMode);
					$this->text .= $value;
				}
			}
		}

		if ($seq !== '') {
			$bitstream = $this->createBinaryStream($this->text, $seq);
			$this->setData($bitstream);
		}

		$this->addDefaultLabel();
	}

 
	public function draw($im) {
		$c = count($this->data);
		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->data[$i], true);
		}

		$this->drawChar($im, '1', true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

 
	public function getDimension($w, $h) {
		// Contains start + text + checksum + stop
		$textlength = count($this->data) * 11;
		$endlength = 2; // + final bar

		$w += $textlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}
 
	protected function validate() {
		$c = count($this->data);
		if ($c === 0) {
			throw new BarcodeException('code128 No data has been entered.');
		}

		parent::validate();
	}

 
	protected function calculateChecksum() {
 
		$this->checksumValue = $this->indcheck[0];
		$c = count($this->indcheck);
		for ($i = 1; $i < $c; $i++) {
			$this->checksumValue += $this->indcheck[$i] * $i;
		}

		$this->checksumValue = $this->checksumValue % 103;
	}
 
	protected function processChecksum() {
		if ($this->checksumValue === false) { // Calculate the checksum only once
			$this->calculateChecksum();
		}

		if ($this->checksumValue !== false) {
			return $this->keys[$this->checksumValue];
		}

		return false;
	}

 
	private function setStartFromText($text) {
		if ($this->starting_text === NULL) {
			if (is_array($text)) {
				if (is_array($text[0])) {
					$this->starting_text = $this->METHOD[$text[0][0]];
					return;
				} else {
					if (is_string($text[0])) {
						$text = $text[0];
					} else {
						$this->starting_text = $this->METHOD[$text[0]];
						return;
					}
				}
			}

			$tmp = preg_quote($this->keysC, '/');
			if (strlen($text) >= 4 && preg_match('/[' . $tmp . ']/', substr($text, 0, 4))) {
				$this->starting_text = 'C';
			} else {
				if (strpos($this->keysB, $text[0])) {
					$this->starting_text = 'B';
				} else {
					$this->starting_text = 'A';
				}
			}
		}
	}
 
	private function extractTilde($text, $pos) {
		if ($text[$pos] === '~') {
			if (isset($text[$pos + 1])) {
				// Do we have a tilde?
				if ($text[$pos + 1] === '~') {
					return '~~';
				} elseif ($text[$pos + 1] === 'F') {
					// Do we have a number after?
					if (isset($text[$pos + 2])) {
						$v = intval($text[$pos + 2]);
						if ($v >= 1 && $v <= 4) {
							return '~F' . $v;
						} else {
							throw new BarcodeException('code128 Bad ~F. You must provide a number from 1 to 4.');
						}
					} else {
						throw new BarcodeException('code128 Bad ~F. You must provide a number from 1 to 4.');
					}
				} else {
					throw new BarcodeException('code128 Wrong code after the ~.');
				}
			} else {
				throw new BarcodeException('code128 Wrong code after the ~.');
			}
		} else {
			throw new BarcodeException('code128 There is no ~ at this location.');
		}
	}

 
	private function getSequenceParsed($text, $currentMode) {
		if ($this->tilde) {
			$sequence = '';
			$previousPos = 0;
			while (($pos = strpos($text, '~', $previousPos)) !== false) {
				$tildeData = $this->extractTilde($text, $pos);

				$simpleTilde = ($tildeData === '~~');
				if ($simpleTilde && $currentMode !== 'B') {
					throw new BarcodeException('code128 The Table ' . $currentMode . ' doesn\'t contain the character ~.');
				}

				// At this point, we know we have ~Fx
				if ($tildeData !== '~F1' && $currentMode === 'C') {
					// The mode C doesn't support ~F2, ~F3, ~F4
					throw new BarcodeException('code128 The Table C doesn\'t contain the function ' . $tildeData . '.');
				}

				$length = $pos - $previousPos;
				if ($currentMode === 'C') {
					if ($length % 2 === 1) {
						throw new BarcodeException('code128 The text "'.$text.'" must have an even number of character to be encoded in Table C.');
					}
				}

				$sequence .= str_repeat('.', $length);
				$sequence .= '.';
				$sequence .= (!$simpleTilde) ? 'F' : '';
				$previousPos = $pos + strlen($tildeData);
			}

			// Flushing
			$length = strlen($text) - $previousPos;
			if ($currentMode === 'C') {
				if ($length % 2 === 1) {
					throw new BCGParseException('code128 The text "'.$text.'" must have an even number of character to be encoded in Table C.');
				}
			}

			$sequence .= str_repeat('.', $length);

			return $sequence;
		} else {
			return str_repeat('.', strlen($text));
		}
	}
 
	private function setParseA($text, &$currentMode) {
		$tmp = preg_quote($this->keysA, '/');

		// If we accept the ~ for special character, we must allow it.
		if ($this->tilde) {
			$tmp .= '~';
		}

		$match = array();
		if (preg_match('/[^' . $tmp . ']/', $text, $match) === 1) {
			// We found something not allowed
			throw new BarcodeException('code128 The text "' . $text . '" can\'t be parsed with the Table A. The character "' . $match[0] . '" is not allowed.');
		} else {
			$latch = ($currentMode === 'A') ? '' : '0';
			$currentMode = 'A';

			return $latch . $this->getSequenceParsed($text, $currentMode);
		}
	}
 
	private function setParseB($text, &$currentMode) {
		$tmp = preg_quote($this->keysB, '/');

		$match = array();
		if (preg_match('/[^' . $tmp . ']/', $text, $match) === 1) {
			// We found something not allowed
			throw new BarcodeException('code128 The text "'.$text.'" can\'t be parsed with the Table B. The character "' . $match[0] . '" is not allowed.');
		} else {
			$latch = ($currentMode === 'B') ? '' : '1';
			$currentMode = 'B';

			return $latch . $this->getSequenceParsed($text, $currentMode);
		}
	}
 
	private function setParseC($text, &$currentMode) {
		$tmp = preg_quote($this->keysC, '/');

		// If we accept the ~ for special character, we must allow it.
		if ($this->tilde) {
			$tmp .= '~F';
		}

		$match = array();
		if (preg_match('/[^' . $tmp . ']/', $text, $match) === 1) {
			// We found something not allowed
			throw new BarcodeException('code128 The text "'.$text.'" can\'t be parsed with the Table C. The character "' . $match[0] . '" is not allowed.');
		} else {
			$latch = ($currentMode === 'C') ? '' : '2';
			$currentMode = 'C';

			return $latch . $this->getSequenceParsed($text, $currentMode);
		}
	}
 
	private function getSequence(&$text, &$starting_text) {
		$e = 10000;
		$latLen = array(
			array(0, 1, 1),
			array(1, 0, 1),
			array(1, 1, 0)
		);
		$shftLen = array(
			array($e, 1, $e),
			array(1, $e, $e),
			array($e, $e, $e)
		);
		$charSiz = array(2, 2, 1);

		$startA = $e;
		$startB = $e;
		$startC = $e;
		if ($starting_text === 'A') $startA = 0;
		if ($starting_text === 'B') $startB = 0;
		if ($starting_text === 'C') $startC = 0;

		$curLen = array($startA, $startB, $startC);
		$curSeq = array(null, null, null);

		$nextNumber = false;

		$x = 0;
		$xLen = strlen($text);
		for ($x = 0; $x < $xLen; $x++) {
			$input = $text[$x];

			// 1.
			for ($i = 0; $i < 3; $i++) {
				for ($j = 0; $j < 3; $j++) {
					if (($curLen[$i] + $latLen[$i][$j]) < $curLen[$j]) {
						$curLen[$j] = $curLen[$i] + $latLen[$i][$j];
						$curSeq[$j] = $curSeq[$i] . $j;
					}
				}
			}

			// 2.
			$nxtLen = array($e, $e, $e);
			$nxtSeq = array();

			// 3.
			$flag = false;
			$posArray = array();

			// Special case, we do have a tilde and we process them
			if ($this->tilde && $input === '~') {
				$tildeData = $this->extractTilde($text, $x);

				if ($tildeData === '~~') {
					// We simply skip a tilde
					$posArray[] = 1;
					$x++;
				} elseif (substr($tildeData, 0, 2) === '~F') {
					$v = intval($tildeData[2]);
					$posArray[] = 0;
					$posArray[] = 1;
					if ($v === 1) {
						$posArray[] = 2;
					}

					$x += 2;
					$flag = true;
				}
			} else {
				$pos = strpos($this->keysA, $input);
				if ($pos !== false) {
					$posArray[] = 0;
				}

				$pos = strpos($this->keysB, $input);
				if ($pos !== false) {
					$posArray[] = 1;
				}

				// Do we have the next char a number?? OR a ~F1
				$pos = strpos($this->keysC, $input);
				if ($nextNumber || ($pos !== false && isset($text[$x + 1]) && strpos($this->keysC, $text[$x + 1]) !== false)) {
					$nextNumber = !$nextNumber;
					$posArray[] = 2;
				}
			}

			$c = count($posArray);
			for ($i = 0; $i < $c; $i++) {
				if (($curLen[$posArray[$i]] + $charSiz[$posArray[$i]]) < $nxtLen[$posArray[$i]]) {
					$nxtLen[$posArray[$i]] = $curLen[$posArray[$i]] + $charSiz[$posArray[$i]];
					$nxtSeq[$posArray[$i]] = $curSeq[$posArray[$i]] . '.';
				}

				for ($j = 0; $j < 2; $j++) {
					if ($j === $posArray[$i]) continue;
					if (($curLen[$j] + $shftLen[$j][$posArray[$i]] + $charSiz[$posArray[$i]]) < $nxtLen[$j]) {
						$nxtLen[$j] = $curLen[$j] + $shftLen[$j][$posArray[$i]] + $charSiz[$posArray[$i]];
						$nxtSeq[$j] = $curSeq[$j] . chr($posArray[$i] + 65) . '.';
					}
				}
			}

			if ($c === 0) {
				// We found an unsuported character
				throw new BarcodeException('code128 Character ' .  $input . ' not supported.');
			}

			if ($flag) {
				for ($i = 0; $i < 5; $i++) {
					if (isset($nxtSeq[$i])) {
						$nxtSeq[$i] .= 'F';
					}
				}
			}

			// 4.
			for ($i = 0; $i < 3; $i++) {
				$curLen[$i] = $nxtLen[$i];
				if (isset($nxtSeq[$i])) {
					$curSeq[$i] = $nxtSeq[$i];
				}
			}
		}

		// Every curLen under $e is possible but we take the smallest
		$m = $e;
		$k = -1;
		for ($i = 0; $i < 3; $i++) {
			if ($curLen[$i] < $m) {
				$k = $i;
				$m = $curLen[$i];
			}
		}

		if ($k === -1) {
			return '';
		}

		$starting_text = chr($k + 65);

		return $curSeq[$k];
	}

 
	private function createBinaryStream($text, $seq) {
		$c = strlen($seq);

		$data = array(); // code stream
		$indcheck = array(); // index for checksum

		$currentEncoding = 0;
		if ($this->starting_text === 'A') {
			$currentEncoding = 0;
			$indcheck[] = self::KEY_STARTA;
		} elseif ($this->starting_text === 'B') {
			$currentEncoding = 1;
			$indcheck[] = self::KEY_STARTB;
		} elseif ($this->starting_text === 'C') {
			$currentEncoding = 2;
			$indcheck[] = self::KEY_STARTC;
		}

		$data[] = $this->code[103 + $currentEncoding];

		$temporaryEncoding = -1;
		for ($i = 0, $counter = 0; $i < $c; $i++) {
			$input = $seq[$i];
			$inputI = intval($input);
			if ($input === '.') {
				$this->encodeChar($data, $currentEncoding, $seq, $text, $i, $counter, $indcheck);
				if ($temporaryEncoding !== -1) {
					$currentEncoding = $temporaryEncoding;
					$temporaryEncoding = -1;
				}
			} elseif ($input >= 'A' && $input <= 'B') {
				// We shift
				$encoding = ord($input) - 65;
				$shift = $this->shift[$currentEncoding][$encoding];
				$indcheck[] = $shift;
				$data[] = $this->code[$shift];
				if ($temporaryEncoding === -1) {
					$temporaryEncoding = $currentEncoding;
				}

				$currentEncoding = $encoding;
			} elseif ($inputI >= 0 && $inputI < 3) {
				$temporaryEncoding = -1;

				// We latch
				$latch = $this->latch[$currentEncoding][$inputI];
				if ($latch !== NULL) {
					$indcheck[] = $latch;
					$data[] = $this->code[$latch];
					$currentEncoding = $inputI;
				}
			}
		}

		return array($indcheck, $data);
	}

 
	private function encodeChar(&$data, $encoding, $seq, $text, &$i, &$counter, &$indcheck) {
		if (isset($seq[$i + 1]) && $seq[$i + 1] === 'F') {
			// We have a flag !!
			if ($text[$counter + 1] === 'F') {
				$number = $text[$counter + 2];
				$fnc = $this->fnc[$encoding][$number - 1];
				$indcheck[] = $fnc;
				$data[] = $this->code[$fnc];

				// Skip F + number
				$counter += 2;
			} else {
				// Not supposed
			}

			$i++;
		} else {
			if ($encoding === 2) {
				// We take 2 numbers in the same time
				$code = (int)substr($text, $counter, 2);
				$indcheck[] = $code;
				$data[] = $this->code[$code];
				$counter++;
				$i++;
			} else {
				$keys = ($encoding === 0) ? $this->keysA : $this->keysB;
				$pos = strpos($keys, $text[$counter]);
				$indcheck[] = $pos;
				$data[] = $this->code[$pos];
			}
		}

		$counter++;
	}
 
	private function setData($data) {
		$this->indcheck = $data[0];
		$this->data = $data[1];
		$this->calculateChecksum();
		$this->data[] = $this->code[$this->checksumValue];
		$this->data[] = $this->code[self::KEY_STOP];
	}
}

class Code39 extends Barcode1D {
	protected $starting, $ending;
	protected $checksum;
 
	public function __construct() {
		parent::__construct();

		$this->starting = $this->ending = 43;
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%', '*');
		$this->code = array(	// 0 added to add an extra space
			'0001101000',	/* 0 */
			'1001000010',	/* 1 */
			'0011000010',	/* 2 */
			'1011000000',	/* 3 */
			'0001100010',	/* 4 */
			'1001100000',	/* 5 */
			'0011100000',	/* 6 */
			'0001001010',	/* 7 */
			'1001001000',	/* 8 */
			'0011001000',	/* 9 */
			'1000010010',	/* A */
			'0010010010',	/* B */
			'1010010000',	/* C */
			'0000110010',	/* D */
			'1000110000',	/* E */
			'0010110000',	/* F */
			'0000011010',	/* G */
			'1000011000',	/* H */
			'0010011000',	/* I */
			'0000111000',	/* J */
			'1000000110',	/* K */
			'0010000110',	/* L */
			'1010000100',	/* M */
			'0000100110',	/* N */
			'1000100100',	/* O */
			'0010100100',	/* P */
			'0000001110',	/* Q */
			'1000001100',	/* R */
			'0010001100',	/* S */
			'0000101100',	/* T */
			'1100000010',	/* U */
			'0110000010',	/* V */
			'1110000000',	/* W */
			'0100100010',	/* X */
			'1100100000',	/* Y */
			'0110100000',	/* Z */
			'0100001010',	/* - */
			'1100001000',	/* . */
			'0110001000',	/*   */
			'0101010000',	/* $ */
			'0101000100',	/* / */
			'0100010100',	/* + */
			'0001010100',	/* % */
			'0100101000'	/* * */
		);

		$this->setChecksum(false);
	}
 
	public function setChecksum($checksum) {
		$this->checksum = (bool)$checksum;
	}
 
	public function parse($text) {
		parent::parse(strtoupper($text));	// Only Capital Letters are Allowed
	}
 
	public function draw($im) {
		// Starting *
		$this->drawChar($im, $this->code[$this->starting], true);
		
		// Chars
		$c =  strlen($this->text);
		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->findCode($this->text[$i]), true);
		}

		// Checksum (rarely used)
		if ($this->checksum === true) {
			$this->calculateChecksum();
			$this->drawChar($im, $this->code[$this->checksumValue % 43], true);
		}

		// Ending *
		$this->drawChar($im, $this->code[$this->ending], true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}
 
	public function getDimension($w, $h) {
		$textlength = 13 * strlen($this->text);
		$startlength = 13;
		$checksumlength = 0;
		if ($this->checksum === true) {
			$checksumlength = 13;
		}

		$endlength = 13;

		$w += $startlength + $textlength + $checksumlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}
 
	protected function validate() {
		$c = strlen($this->text);
		if ($c === 0) {
			throw new BCGParseException('code39', 'No data has been entered.');
		}

		// Checking if all chars are allowed
		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BarcodeException('code39 The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}
		
		if (strpos($this->text, '*') !== false) {
			throw new BarcodeException('code39 The character \'*\' is not allowed.');
		}

		parent::validate();
	}
 
	protected function calculateChecksum() {
		$this->checksumValue = 0;
		$c = strlen($this->text);
		for ($i = 0; $i < $c; $i++) {
			$this->checksumValue += $this->findIndex($this->text[$i]);
		}

		$this->checksumValue = $this->checksumValue % 43;
	}

 
	protected function processChecksum() {
		if ($this->checksumValue === false) { // Calculate the checksum only once
			$this->calculateChecksum();
		}

		if ($this->checksumValue !== false) {
			return $this->keys[$this->checksumValue];
		}

		return false;
	}
}

