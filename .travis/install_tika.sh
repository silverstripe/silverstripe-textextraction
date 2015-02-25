#!/usr/bin/env bash

## Install tika app
wget 'https://archive.apache.org/dist/tika/tika-app-1.7.jar' -O "$HOME/bin/tika.jar"
echo -e '#!/usr/bin/env bash\nexec java -jar $HOME/bin/tika.jar "$@"' >> $HOME/bin/tika
chmod ug+x $HOME/bin/tika
$HOME/bin/tika --version


## Install tika server
wget 'https://archive.apache.org/dist/tika/tika-server-1.7.jar' -O "$HOME/bin/tika-rest-server.jar"
echo -e '#!/usr/bin/env bash\nexec java -jar $HOME/bin/tika-rest-server.jar "$@"' >> $HOME/bin/tika-rest-server
chmod ug+x $HOME/bin/tika-rest-server

