## Geek Off The Street Specification

### Overview
The data from regulations.gov, specifically the comment data, is too hard to get. 
I want to make it possible for geeks to download massive amounts of comment data (in its 'native' JSON) formats easily


### Non goals
I do not much care about: 

* Having a browsing interface of the data. That is what [docket wrench](https://sunlightfoundation.com/2013/01/31/docket-wrench-exposing-trends-regulatory-comments/) is, and this is not that. 


### Interfaces

* Several torrent files. That allow for the downloading of very large chunks of docket, comment data. 
* API that closely mimics the one available from [Regulations.gov API](https://regulationsgov.github.io/developers/)

### Problems to Solve

#### How to enumerate and then get all of the data

Problems getting the data: 

* Lots of old historical data.
* Frequently updated data. 

* SETI at home style: have participants who are dynamically assigned new parts of dataset to download. 
* run the current program for a long time. (playing catchup)
* have a static group sharing the load, basically the same as first one but split into parts. 
* RECAP style: implement a regulations.gov api layer, that automatically mirrors when possible.

Answer: Have one key devoted to tracking what is happening now, and distributing the download work to other nodes. 

#### How cheaply and conviently redistribute the data?

Problem: Hosting can get expensive.

* use zip to compress things


