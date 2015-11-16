Colorframe
==========

A PHP console application to generate averaged color hex codes from video frames.

The hexcode json can be used to create movie color bars as seen on sites like http://moviebarcode.tumblr.com and http://thecolorsofmotion.com. This app only generates one color averaged hex code per frame. 

Requirements
============

* PHP: >=5.5.9
* FFMpeg \ FFProbe

Installation
============

Install FFMpeg and FFProbe binaries. This app has only been tested on windows so far. You can find the windows binaries for FFMpeg [here](http://ffmpeg.zeranoe.com/builds/).

Run composer install on the colorframe directory

Usage
=====

Place mp4 video files in the /videos folder.

From the command line, cd into the colorframe directory...

    $ colorframe process --v=video.mp4 --w=1280

where --v is the name of the video file and --w is the width in pixels of your final color bar. --w is optional and default is set to 1280. 

The Hex colors will be saved to a json file named video_1280.json in the /files folder.

License
=======

This project is licensed under the [MIT license](http://opensource.org/licenses/MIT).


