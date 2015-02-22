#!/usr/bin/env bash
mkdir $HOME/bin
wget 'http://www.us.apache.org/dist/tika/tika-app-1.7.jar' -O "$HOME/bin/tika.jar"
echo -e '#!/usr/bin/env bash\nexec java -jar $HOME/bin/tika.jar "$@"' >> $HOME/bin/tika
chmod ug+x $HOME/bin/tika
$HOME/bin/tika --version
