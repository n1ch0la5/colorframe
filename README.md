## Basic Usage

ffmpeg needs to be installed first.

Place mp4 video files in the /videos folder.

From the command line, cd into the colorframe directory...

```
$ colorframe process --v=video.mp4 --w=1280
```

where --v is the name of your video file and --w is the width in pixels of your final color bar. --w is optional and default is set to 1280. 

The Hex colors will be saved to a json file named video_1280.json in the /files folder.
