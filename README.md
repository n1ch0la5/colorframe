ColorFrame
==========

A PHP console application to generate averaged color hex codes (#000000) from video frames.

The hex code json file can then be used to create movie color bars as seen on sites like http://moviebarcode.tumblr.com and http://thecolorsofmotion.com. This app only generates one color averaged hex code per frame per one second of video.

Requirements
============

* PHP: >=5.5.9
* FFMpeg \ FFProbe binaries

Installation
============

php-ffmpeg requires installation of the FFMpeg and FFProbe binaries. This app has thus far only been tested on mp4 video files on a  Windows machine. You can find the windows binaries for FFMpeg [here](http://ffmpeg.zeranoe.com/builds/). Download the static binaries, unzip the contents to a folder and put the path to the folder in your system PATH. (C:\ffmpeg or C:\Users\<USERNAME>\ffmpeg for example).

Usage
=====

Place an mpeg video file in the /videos folder.

From the command line, cd into the colorframe directory...

    $ cf process --v=video.mp4 --w=1280

where --v is the name of the video file and --w is the width in pixels of your final color bar. --w is optional and default is set to 1280. 

The Hex colors will be saved to a json file named video_1280.json in the /files folder.

License
=======

This project is licensed under the [MIT license](http://opensource.org/licenses/MIT).