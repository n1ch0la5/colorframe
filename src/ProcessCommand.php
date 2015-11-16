<?php namespace n1ch0la5;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Coordinate\TimeCode;

class ProcessCommand extends Command {
    
    private $ffMpeg;
    private $ffProbe;
    
    public function __construct(FFMpeg $ffMpeg, FFProbe $ffProbe)
    {
        parent::__construct();
        $this->ffmpeg = $ffMpeg;
        $this->ffprobe = $ffProbe;
    }
    
    public function configure()
    {
        $this->setName('process')
             ->setDescription('Process video frames into colored bars.')
             ->addOption('v', null, InputOption::VALUE_REQUIRED)
             ->addOption('w', null, InputOption::VALUE_OPTIONAL, 'Set the width of the final image. Default is 1280', "1280");
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Set video path
        $vidPath = getcwd() . '/videos/';
        $vidName = $input->getOption('v');
        $width = $input->getOption('w');
        
        $this->assertVideoExists($vidPath . $vidName, $output);
        
        $format = $this->ffprobe->format($vidPath . $vidName);
        $duration = floor($format->get('duration'));
        
        $output->writeln('Processing ' . $duration . ' frames...');
        
        if( $duration > $width)
        {
            $frameSet = $duration / $width;
            $frameSet = round($frameSet);
        }
        else
        {
            $frameSet = 1;
        }
        
        $video = $this->ffmpeg->open($vidPath.$vidName);
        //$video->save($format, getcwd() . 'video2.mp4');
        
        $framePath = $this->createFramePath($vidName);
        
        $n=0;
        $fs=0;
        for($i=0;$i<=$duration;$i++)
        {
            
            //$frame = $video->frame( FFMpeg\Coordinate\TimeCode::fromSeconds($i) );
            $tc = new Timecode(0,0,0,0);
            $frame = $video->frame( $tc::fromSeconds($i) );

            //$frame = $video->coordinate->timecode->fromSeconds($i);
            
            $imagePath = $this->saveFrame($frame, $framePath, $i);
            
            $colors[] = $this->getFrameColor($imagePath, $frameSet, $n);
            
            $this->showStatus($i + 1, $duration, '25', $output);
            
            if($fs == $frameSet - 1){$fs = 0;$n++;}else{$fs++;}
        }
        
        if($frameSet > 1)
        {
            $colorsNew = $this->colorSorter($colors, $frameSet);
        }
        else
        {
            $colorsNew = $colors;
        }
        
        $file = $this->makeFileName($vidName, $width);
        
        if( $this->saveFile($file, $colorsNew) )
        {
            $output->writeln('New file successfully created in '. $file);
        }
        
        $this->cleanUp($framePath);
    }
    
    private function getTimeCode(TimeCode $timeCode, $seconds)
    {
        $this->timecode = $timeCode->fromSeconds($seconds);
    }
    
    private function saveFile($file, $colorsNew)
    {
        // write colors to file
        $fp = fopen($file, 'w');
        fwrite($fp, json_encode($colorsNew));
        fclose($fp);
    }
    
    private function makeFileName($vidName, $width)
    {
        return 'files/' . str_replace('.mp4', '', $vidName) . '_' . $width . '.json';
    }
    
    private function assertVideoExists($vidPath, OutputInterface $output)
    {
        if( ! file_exists($vidPath) )
        {
            $output->writeln('Video does not exist at ' . $vidPath . '!');
            exit(1);
        }
    }
    
    private function createFramePath($vidName)
    {
        $framePath = 'temp/'. $vidName;
        if(!file_exists($framePath))
        {
            mkdir($framePath);
        }
        return $framePath;
    }
    
    private function saveFrame($frame, $framePath, $i)
    {
        $imagePath = $framePath . '/' . $i . '.jpg';
        $frame->save($imagePath);
        return $imagePath;
    }
    
    private function getFrameColor($imagePath, $frameSet, $n)
    {
        if( $image = imagecreatefromjpeg($imagePath) )
        {   
            $width = imagesx($image);
            $height = imagesy($image);
            $pixel = imagecreatetruecolor(1, 1);
            imagecopyresampled($pixel, $image, 0, 0, 0, 0, 1, 1, $width, $height);
            $rgb = imagecolorat($pixel, 0, 0);

            $color = imagecolorsforindex($pixel, $rgb);
            $hex = $this->rgb2hex( array( $color['red'], $color['green'], $color['blue'] ) );

            if($frameSet == 1)
            {
                $colors[$n] = $hex;
            }
            else
            {
                $colors[$n][] = $hex;
            }
    
            $this->cleanUp($imagePath);
            
            return $colors;
        }
    }
    
    private function rgb2hex($rgb) {
        $hex = "#";
        $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

        return $hex; // returns the hex value including the number sign (#)
    }
    
    private function colorSorter($array, $frameSet)
    {
        global $vidWidth;
        $counter = 0;

        for($i=0;$i<=count($array)-1;$i++)
        {
            $newColor = $array[$i][0];
            for($n=0;$n<=$frameSet-1;$n++)
            {
                $n++;
                $colorAvg = $this->averageColor( $newColor, $array[$i][$n] );
                $newColor = $colorAvg;
            }
            unset($array[$i]);
            $array[$i] = $newColor;
            $counter++;
            if($counter >= $vidWidth - 1)
            {
                echo $counter;
                return $array;
            }
        }    
        return $array;
    }
    
    private function averageColor($color1, $color2) {
        $color = array(array($color1,0,0,0), array($color2,0,0,0), array("#",0,0,0));
        for ($i=0; $i<2; $i++) {
            $offset=0;
            if (strlen($color[$i][0])>6) {
                $offset=1;
            }
            $color[$i][1] = hexdec( substr($color[$i][0], 0+$offset, 2) ); // Red Decimal
            $color[$i][1] = hexdec( substr($color[$i][0], 0+$offset, 2) ); // Red Decimal
            $color[$i][2] = hexdec( substr($color[$i][0], 2+$offset, 2) ); // Green Decimal
            $color[$i][3] = hexdec( substr($color[$i][0], 4+$offset, 2) ); // Blue Decimal
        }
        for ($i=1; $i<4; $i++) {
            $color[2][$i] = round( ($color[0][$i] + $color[1][$i]) / 2 ); 
            $color[2][0] = $color[2][0] . strtoupper( substr("0" . dechex($color[2][$i]), -2) );  // New Average Color Concatenation 
        }

        return ($color[2][0]); //return ($color[2][0]);
}
    
    //Progress bar
    private function showStatus($done, $total, $size=30, OutputInterface $output)
    {
        static $startTime;

        // if we go over our bound, just ignore it
        if($done > $total) return;

        if(empty($startTime)) $startTime=time();
        $now = time();

        $perc=(double)($done/$total);

        $bar=floor($perc*$size);

        $statusBar="\r[";
        $statusBar.=str_repeat("=", $bar);
        if($bar<$size){
            $statusBar.=">";
            $statusBar.=str_repeat(" ", $size-$bar);
        } else {
            $statusBar.="=";
        }

        $disp=number_format($perc*100, 0);

        $statusBar.="] $disp%  $done/$total";

        $rate = ($now-$startTime)/$done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $startTime;

        $statusBar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";

        echo "$statusBar  ";
        $output->writeln("$statusBar  ");

        flush();

        // when done, send a newline
        if($done == $total) {
            $output->writeln( PHP_EOL );
        }
    }
    
    private function cleanUp($path)
    {
        $realpath = realpath($path);
        @chmod($realpath, 0777);
        if(is_writable($realpath))
        {
            @unlink($realpath);
        }
    }
}