<?php
    class TExcel {
        private static $style = [
            "header"=>[
                'font' => [
                    'name'  => 'Tahoma',
                    'size' => 9,
                    // 'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb'=>'000000'],
                    ),
                ),
                'fill'=>[
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb'=>'E1E1E1']
                ]
            ],
            "center"=>[
                'font' => [
                    'name'  => 'Tahoma',
                    'size' => 9,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ],
            "left"=>[
                'font' => [
                    'name'  => 'Tahoma',
                    'size' => 9,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                ]
            ],
            "right"=>[
                'font' => [
                    'name'  => 'Tahoma',
                    'size' => 9,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ]
            ],
        ];
        public static function GetStyleNormalColor($color) {
            return [
                'font' => [
                    'name'  => 'Tahoma',
                    'size' => 9,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill'=>[
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb'=>$color]
                ]
            ];
        }
        public static function SetValue($sheet, $col, $row, $value, $style="center", $merge=null, $isString=false) {
            if( $style=="link" ) {
                $coord = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                // $sheet->setCellValue($coord, $value);
                if($isString) {
                    $sheet->setCellValueExplicit([$col, $row], $value, PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue([$col, $row], $value);
                }
                $sheet->getCell($coord)->getHyperlink()->setUrl($value);
                $link_style = array(
                    'font'  => array(
                      'color' => array('rgb' => '0000FF'),
                      'underline' => 'single'
                    )
                );
                $sheet->getStyle($coord)->applyFromArray($link_style);
            } else {
                if($isString) {
                    $sheet->setCellValueExplicit([$col, $row], $value, PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue([$col, $row], $value);
                }
                if( $merge==null ) {
                    if(is_string($style)) $sheet->getStyle([$col, $row])->applyFromArray(self::$style[$style]);
                    else $sheet->getStyle([$col, $row])->applyFromArray($style);
                } else {
                    $coord = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row . ':' . PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col+$merge[0]) . ($row+$merge[1]);
                    $sheet->mergeCells($coord);
                    if(is_string($style)) $sheet->getStyle($coord)->applyFromArray(self::$style[$style]);
                    else $sheet->getStyle($coord)->applyFromArray($style);
                }
            }
        }
        public static function SetWidth($sheet, $col, $width) {
            $sheet->getColumnDimension(PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col))->setWidth($width);
        }
    }