#!/bin/sh
# Convert PDF to Image
# Pepare verbose Output to "$FILE.ok" for parsing (one line per output file)
# create $FILE.error on error

LOGFILE="/var/log/gen_pdf_preview.log"

# ownership of files
OWNER="www-data"

# PID
PID=$$

# FILE is last arg
shift $(($# - 1))
FILE=$1

DIR=`dirname $FILE`

# run only once
LASTPID=""
if [ -f $FILE.lock ] ; then 
  LASTPID=`cat $FILE.lock`
fi
 
if [ "$LASTPID" != "" ] ; then
  RUNCMD=`cat /proc/$LASTPID/comm`
  if [ "$RUNCMD" = "gen_pdf_preview" ] ; then
    echo Running process with PID $LASTPID
    exit
  else
    echo removing stale pidfile
    rm $FILE.lock
  fi   
fi

echo $PID > $FILE.lock

# -scene 1 start pageindex at 1
# -density: resolution
convert -verbose -density 300 -scene 1 $FILE $FILE.%d.jpg > $FILE.conv 2>&1

# create thumbs and gallery preview
mkdir -p "$DIR/gal"
mkdir -p "$DIR/th"
nconvert -resize 1200 1200 -ratio -q 95 -o $DIR/gal/% $FILE.*.jpg 
nconvert -resize 120 120 -ratio -q 95 -o $DIR/th/% $FILE.*.jpg


# Wenn Datei bereits in User DB, alle Previews verschieben
# Dieser Zustand tritt auf, wenn der Nutzer das PDF speichert, bevor die Voransichten fertig generiert sind 
if [ -f $FILE.moved ] ; then
  # In dieser Datei steht die JID Pfad zum Benutzerverzeichnis
  JID=`cut -f 1 -d" " $FILE.moved`
  PATH_MOVED=`cut -f 2 -d" " $FILE.moved`
  DIR_MOVED=$(dirname "${PATH_MOVED}")
  DIR_USER=$(dirname "${DIR_MOVED}")
  FILENAME=$(basename "${FILE}")
  # Voransichten der Seiten nach JID Konvention umbenennen
  echo ls "$DIR/th/$FILENAME.*.jpg" >> $LOGFILE
  ls $DIR/th/$FILENAME.*.jpg | awk '{moved=$1; gsub(/'$FILENAME'/,"o'$JID'",moved); print "mv "$1" "moved}' > $FILE.rename.sh
  ls $DIR/gal/$FILENAME.*.jpg | awk '{moved=$1; gsub(/'$FILENAME'/,"o'$JID'",moved); print "mv "$1" "moved}' >> $FILE.rename.sh
  sh $FILE.rename.sh
  # Erste Seite als Objekt Voransicht kopieren
  cp $DIR/th/o$JID.1.jpg $DIR/th/o$JID.jpg
  cp $DIR/gal/o$JID.1.jpg $DIR/gal/o$JID.jpg

  # Dateien in User Verzeichnis schieben  
  echo mv $DIR/th/o$JID.*.jpg $DIR_USER/th/ >> $LOGFILE
  echo mv $DIR/gal/o$JID.*.jpg $DIR_USER/gal/ >> $LOGFILE
  mv $DIR/th/o$JID.jpg $DIR_USER/th/
  mv $DIR/gal/o$JID.jpg $DIR_USER/gal/
  mv $DIR/th/o$JID.*.jpg $DIR_USER/th/
  mv $DIR/gal/o$JID.*.jpg $DIR_USER/gal/

fi

# first line should be "gs" command
# grep "error" in first line of output
error=`head -1 $2.conv | grep error`

if [ "$error" = "" ] ; then
  cat "$FILE.conv" | grep "$FILE=>" | sed s/\\[.*\\]//g | sed "s|$FILE=>||g" > $FILE.ok
else 
  echo $error > $FILE.error 
fi


chown $OWNER $DIR/* $DIR/gal/* $DIR/th/* 

rm $FILE.lock
