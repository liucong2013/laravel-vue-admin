<?php
namespace App\Exports;


use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class CodeExport implements FromCollection , WithEvents , WithDrawings
{
    public $data;
    public $param;
    public $code_file;


    public function __construct($model , $param)
    {
        $this->param = $param;
        $this->getData($model);
    }

    //数组转集合
    public function collection()
    {
        return new Collection($this->data);

    }

    /**
     * 注册事件
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class  => function(AfterSheet $event) {

                //列高

                for ($i = 2; $i<=1265; $i++) {
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(150);
                }


                //设置标题格式
              //  $event->sheet->getDelegate()->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size'=>20]]);
              //  $event->sheet->getDelegate()->getStyle('A2:M2')->applyFromArray(['font' => [ 'size'=>14]]);


                //列宽
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(35);

                $event->sheet->getDelegate()->getStyle('A1:K1265')->getAlignment()->setVertical('center');




            }
        ];
    }







    //获取数据
    public function getData($model)
    {

        $cellData[] = ['编码' , '二维码'];


        $data =  $model->where('batch' , $this->param['batch'])
            ->select(['code'])
            ->get()->toArray();


        if(!empty($data)){
            foreach ($data as $val){
                $val['code'] = chunk_split($val['code'] , 4 , ' ');
                $this->code_file[] = Storage::url('qrCode/1/1674664982775.jpg');


                $cellData[] = $val;
            }
        }

        $this->data = $cellData;

    }

    public function drawings()
    {
        $result = [];
        (new Drawing())->setHeight();
        foreach ($this->code_file as $k => $v) {
            $k += 2;
            ${'drawing' . $k} = new Drawing();
            ${'drawing' . $k}->setName('二维码');
            ${'drawing' . $k}->setDescription('二维码');
            //图片路径
            ${'drawing' . $k}->setPath(public_path($v));
            ${'drawing' . $k}->setHeight(150);
            ${'drawing' . $k}->setVertical('middle');
            //设置图片列
            ${'drawing' . $k}->setCoordinates('B' . $k);

            $result[] = ${'drawing' . $k};
        }
        return $result;
    }

    public function getTitleName()
    {
        return '第'.$this->param['batch'].'次编码.xlsx';
    }

}
