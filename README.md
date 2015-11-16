## Basic Usage

ffmpeg needs to be installed first.

Place your mp4 video files in the /videos folder.

Within the colorframe directory on the command line, type...

```
colorframe process --v=video.mp4 --w=1280
```

where --v is the name of your video file and --w is the width in pixels of your final color bar. --w is optional and default is set to 1280

A json file named video_1280.json will be saved in the /files folder.
