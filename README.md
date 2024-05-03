# zawodynaiaseo_scorevideooutput #

## General description and how to use it ## 
This repository contains files necessary for generating graphics with scores for archery competition. It can be use during alternating finals matches.

To use it with your ianseo server you need to put this files in /htdocs/Modules/Custom directory in your ianseo folder(if you are using linux version of ianseo you need to skip htdocs part).

To get access to graphics you need to go to following directory
http://localhost/Modules/Custom/IanseoVideoGraphics_Main.php?tour=XXX

if ianseo is on different server change localhost to ip adress of computer with ianseo
you also need to change XXX to competition code from which you want to generated graphics.  

To add it to your video stream you can add video source->browser in OBS Studio and specify url given above. 

Since any ianseo update can crash working of this repository always remember to test it before competition.