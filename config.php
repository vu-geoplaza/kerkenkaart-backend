<?php

// do not commit
Define('DBHOST', 'kerken-db');
Define('DBPORT', '5432');
Define('DB', 'kerken');
Define('DBUSER', getenv('POSTGRES_USER'));
Define('DBPW', getenv('POSTGRES_PASSWORD'));

Define('BASE_URL_MR', 'https://monumentenregister.cultureelerfgoed.nl/monumenten/');

Define('DENOMS', array(
    "Christelijke Gereformeerde Kerk",
    "Christian Science Church",
    "Doopsgezinde Sociëteit",
    "Evangelisch Lutherse Kerk",
    "Gereformeerde Gemeente (in Nederland)",
    "Gereformeerde Kerk (vrijgemaakt)",
    "Gereformeerde Kerken",
    "Nederlandse Hervormde Kerk",
    "Nederlandse Protestantenbond",
    "Oud-Katholieke Kerk",
    "Remonstrantse Broederschap",
    "Rooms-katholieke Kerk",
    "Overig"
));

?>

