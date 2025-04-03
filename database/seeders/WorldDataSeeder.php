<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WorldDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos de continentes
        $continents = [
            ['name' => 'ÁFRICA', 'code' => 'AFR'],
            ['name' => 'AMÉRICA', 'code' => 'AME'],
            ['name' => 'ASIA', 'code' => 'ASI'],
            ['name' => 'EUROPA', 'code' => 'EUR'],
            ['name' => 'OCEANÍA', 'code' => 'OCE'],
        ];

        $continentIds = [];
        foreach ($continents as $continent) {
            $id = DB::table('continents')->insertGetId([
                'name' => $continent['name'],
                'code' => $continent['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $continentIds[$continent['name']] = $id;
        }



        $countriesData = [
            'ÁFRICA' => [
                'ANGOLA' => ['code' => 'AGO', 'cities' => ['LUANDA']],
                'ARGELIA' => ['code' => 'DZA', 'cities' => ['ARGEL']],
                'BENIN' => ['code' => 'BEN', 'cities' => ['PORTO-NOVO']],
                'BOTSUANA' => ['code' => 'BWA', 'cities' => ['GABERONES']],
                'BURKINA FASO' => ['code' => 'BFA', 'cities' => ['UAGADUGÚ']],
                'BURUNDI' => ['code' => 'BDI', 'cities' => ['BUYUMBURA']],
                'CABO VERDE' => ['code' => 'CPV', 'cities' => ['PRAIA']],
                'CAMERÚN' => ['code' => 'CMR', 'cities' => ['YAUNDÉ']],
                'CHAD' => ['code' => 'TCD', 'cities' => ['YAMENA']],
                'COMORAS' => ['code' => 'COM', 'cities' => ['MORONI']],
                'COSTA DE MARFIL' => ['code' => 'CIV', 'cities' => ['YAMUSUKRO', 'ABIYÁN']],
                'EGIPTO' => ['code' => 'EGY', 'cities' => ['EL CAIRO']],
                'ERITREA' => ['code' => 'ERI', 'cities' => ['ASMARA']],
                'ETIOPÍA' => ['code' => 'ETH', 'cities' => ['ADÍS ABEBA']],
                'GABÓN' => ['code' => 'GAB', 'cities' => ['LIBREVILLE']],
                'GAMBIA' => ['code' => 'GMB', 'cities' => ['BANJUL']],
                'GHANA' => ['code' => 'GHA', 'cities' => ['ACCRA']],
                'GUINEA' => ['code' => 'GIN', 'cities' => ['CONAKRY']],
                'GUINEA ECUATORIAL' => ['code' => 'GNQ', 'cities' => ['MALABO']],
                'GUINEA-BISSAU' => ['code' => 'GNB', 'cities' => ['BISSAU']],
                'KENIA' => ['code' => 'KEN', 'cities' => ['NAIROBI']],
                'LESOTO' => ['code' => 'LSO', 'cities' => ['MASERU']],
                'LIBERIA' => ['code' => 'LBR', 'cities' => ['MONROVIA']],
                'LIBIA' => ['code' => 'LBY', 'cities' => ['TRÍPOLI']],
                'MADAGASCAR' => ['code' => 'MDG', 'cities' => ['ANTANANARIVO']],
                'MALAUI' => ['code' => 'MWI', 'cities' => ['LILONGÜE']],
                'MALI' => ['code' => 'MLI', 'cities' => ['BAMAKO']],
                'MARRUECOS' => ['code' => 'MAR', 'cities' => ['RABAT']],
                'MAURICIO' => ['code' => 'MUS', 'cities' => ['PORT LOUIS']],
                'MAURITANIA' => ['code' => 'MRT', 'cities' => ['NUAKCHOT']],
                'MOZAMBIQUE' => ['code' => 'MOZ', 'cities' => ['MAPUTO']],
                'NAMIBIA' => ['code' => 'NAM', 'cities' => ['WINDHOEK']],
                'NÍGER' => ['code' => 'NER', 'cities' => ['NIAMEY']],
                'NIGERIA' => ['code' => 'NGA', 'cities' => ['ABUYA']],
                'REPÚBLICA CENTROAFRICANA' => ['code' => 'CAF', 'cities' => ['BANGUI']],
                'REPÚBLICA DEL CONGO' => ['code' => 'COG', 'cities' => ['BRAZZAVILLE']],
                'REPÚBLICA DEMOCRÁTICA DEL CONGO' => ['code' => 'COD', 'cities' => ['KINSHASA']],
                'REPÚBLICA SAHARAUI' => ['code' => 'ESH', 'cities' => ['EL AAIÚN']],
                'RUANDA' => ['code' => 'RWA', 'cities' => ['KIGALI']],
                'SANTO TOMÉ Y PRÍNCIPE' => ['code' => 'STP', 'cities' => ['SANTO TOMÉ']],
                'SENEGAL' => ['code' => 'SEN', 'cities' => ['DAKAR']],
                'SEYCHELLES' => ['code' => 'SYC', 'cities' => ['VICTORIA']],
                'SIERRA LEONA' => ['code' => 'SLE', 'cities' => ['FREETOWN']],
                'SOMALIA' => ['code' => 'SOM', 'cities' => ['MOGADISCIO']],
                'SUAZILANDIA' => ['code' => 'SWZ', 'cities' => ['MBABANE']],
                'SUDÁFRICA' => ['code' => 'ZAF', 'cities' => ['PRETORIA', 'CIUDAD DEL CABO', 'BLOEMFONTEIN']],
                'SUDÁN' => ['code' => 'SDN', 'cities' => ['JARTUM']],
                'SUDÁN DEL SUR' => ['code' => 'SSD', 'cities' => ['YUBA']],
                'TANZANIA' => ['code' => 'TZA', 'cities' => ['DODOMA']],
                'TOGO' => ['code' => 'TGO', 'cities' => ['LOMÉ']],
                'TÚNEZ' => ['code' => 'TUN', 'cities' => ['TÚNEZ']],
                'UGANDA' => ['code' => 'UGA', 'cities' => ['KAMPALA']],
                'YIBUTI' => ['code' => 'DJI', 'cities' => ['YIBUTI']],
                'ZAMBIA' => ['code' => 'ZMB', 'cities' => ['LUSAKA']],
                'ZIMBABUE' => ['code' => 'ZWE', 'cities' => ['HARARE']],
            ],
            'AMÉRICA' => [
                'ANTIGUA Y BARBUDA' => ['code' => 'ATG', 'cities' => ['SAINT JOHN\'S']],
                'ARGENTINA' => ['code' => 'ARG', 'cities' => ['BUENOS AIRES']],
                'BAHAMAS' => ['code' => 'BHS', 'cities' => ['NASSAU']],
                'BARBADOS' => ['code' => 'BRB', 'cities' => ['BRIDGETOWN']],
                'BELICE' => ['code' => 'BLZ', 'cities' => ['BELMOPÁN']],
                'BOLIVIA' => ['code' => 'BOL', 'cities' => ['LA PAZ', 'SUCRE', 'SANTA CRUZ', 'COCHABAMBA']],
                'BRASIL' => ['code' => 'BRA', 'cities' => ['BRASILIA', 'SAO PABLO']],
                'CANADÁ' => ['code' => 'CAN', 'cities' => ['OTTAWA']],
                'CHILE' => ['code' => 'CHL', 'cities' => ['SANTIAGO DE CHILE', 'ARICA', 'IQUIQUE']],
                'COLOMBIA' => ['code' => 'COL', 'cities' => ['BOGOTÁ', 'CALI', 'MEDELLIN']],
                'COSTA RICA' => ['code' => 'CRI', 'cities' => ['SAN JOSÉ']],
                'CUBA' => ['code' => 'CUB', 'cities' => ['LA HABANA']],
                'DOMINICA' => ['code' => 'DMA', 'cities' => ['ROSEAU']],
                'ECUADOR' => ['code' => 'ECU', 'cities' => ['QUITO']],
                'EL SALVADOR' => ['code' => 'SLV', 'cities' => ['SAN SALVADOR']],
                'ESTADOS UNIDOS' => ['code' => 'USA', 'cities' => ['WASHINGTON D. C.', 'MIAMI', 'NEW YORK']],
                'GRANADA' => ['code' => 'GRD', 'cities' => ['SAINT GEORGE\'S']],
                'GUATEMALA' => ['code' => 'GTM', 'cities' => ['CIUDAD DE GUATEMALA']],
                'GUYANA' => ['code' => 'GUY', 'cities' => ['GEORGETOWN']],
                'HAITÍ' => ['code' => 'HTI', 'cities' => ['PUERTO PRÍNCIPE']],
                'HONDURAS' => ['code' => 'HND', 'cities' => ['TEGUCIGALPA']],
                'JAMAICA' => ['code' => 'JAM', 'cities' => ['KINGSTON']],
                'MÉXICO' => ['code' => 'MEX', 'cities' => ['MÉXICO D. F.']],
                'NICARAGUA' => ['code' => 'NIC', 'cities' => ['MANAGUA']],
                'PANAMÁ' => ['code' => 'PAN', 'cities' => ['CIUDAD DE PANAMÁ']],
                'PARAGUAY' => ['code' => 'PRY', 'cities' => ['ASUNCIÓN']],
                'PERÚ' => ['code' => 'PER', 'cities' => ['LIMA']],
                'PUERTO RICO' => ['code' => 'PRI', 'cities' => ['SAN JUAN']],
                'REPÚBLICA DOMINICANA' => ['code' => 'DOM', 'cities' => ['SANTO DOMINGO']],
                'SAN CRISTÓBAL Y NIEVES' => ['code' => 'KNA', 'cities' => ['BASSETERRE']],
                'SAN VICENTE Y LAS GRANADINAS' => ['code' => 'VCT', 'cities' => ['KINGSTOWN']],
                'SANTA LUCÍA' => ['code' => 'LCA', 'cities' => ['CASTRIES']],
                'SURINAM' => ['code' => 'SUR', 'cities' => ['PARAMARIBO']],
                'TRINIDAD Y TOBAGO' => ['code' => 'TTO', 'cities' => ['PUERTO ESPAÑA']],
                'URUGUAY' => ['code' => 'URY', 'cities' => ['MONTEVIDEO']],
                'VENEZUELA' => ['code' => 'VEN', 'cities' => ['CARACAS']],
            ],
            'ASIA' => [
                'AFGANISTÁN' => ['code' => 'AFG', 'cities' => ['KABUL']],
                'ARABIA SAUDITA' => ['code' => 'SAU', 'cities' => ['RIAD']],
                'BANGLADÉS' => ['code' => 'BGD', 'cities' => ['DACA']],
                'BARÉIN' => ['code' => 'BHR', 'cities' => ['MANAMÁ']],
                'BRUNEI' => ['code' => 'BRN', 'cities' => ['BANDAR SERI BEGAWAN']],
                'BUTÁN' => ['code' => 'BTN', 'cities' => ['TIMBU']],
                'CAMBOYA' => ['code' => 'KHM', 'cities' => ['PNOM PENH']],
                'CATAR' => ['code' => 'QAT', 'cities' => ['DOHA']],
                'CHINA' => ['code' => 'CHN', 'cities' => ['PEKÍN', 'GUANGZHOU', 'SHANGHAI', 'BEIJING', 'SHENZHEN', 'HONG KONG', 'FOSHAN', 'TIANJIN', 'NINGBO', 'XIAMEN']],
                'CHIPRE' => ['code' => 'CYP', 'cities' => ['NICOSIA']],
                'COREA DEL NORTE' => ['code' => 'PRK', 'cities' => ['PYONGYANG']],
                'COREA DEL SUR' => ['code' => 'KOR', 'cities' => ['SEÚL']],
                'EMIRATOS ARABES UNIDOS' => ['code' => 'ARE', 'cities' => ['ABU DABI']],
                'FILIPINAS' => ['code' => 'PHL', 'cities' => ['MANILA']],
                'INDIA' => ['code' => 'IND', 'cities' => ['NUEVA DELHI']],
                'INDONESIA' => ['code' => 'IDN', 'cities' => ['YAKARTA']],
                'IRÁN' => ['code' => 'IRN', 'cities' => ['TEHERÁN']],
                'IRAQ' => ['code' => 'IRQ', 'cities' => ['BAGDAD']],
                'ISRAEL' => ['code' => 'ISR', 'cities' => ['JERUSALÉN']],
                'JAPÓN' => ['code' => 'JPN', 'cities' => ['TOKIO']],
                'JORDANIA' => ['code' => 'JOR', 'cities' => ['AMMÁN']],
                'KAZAJISTÁN' => ['code' => 'KAZ', 'cities' => ['ASTANÁ']],
                'KIRGUISTÁN' => ['code' => 'KGZ', 'cities' => ['BISKEK']],
                'KUWAIT' => ['code' => 'KWT', 'cities' => ['CIUDAD DE KUWAIT']],
                'LAOS' => ['code' => 'LAO', 'cities' => ['VIENTIÁN']],
                'LÍBANO' => ['code' => 'LBN', 'cities' => ['BEIRUT']],
                'MALASIA' => ['code' => 'MYS', 'cities' => ['KUALA LUMPUR']],
                'MALDIVAS' => ['code' => 'MDV', 'cities' => ['MALÉ']],
                'MONGOLIA' => ['code' => 'MNG', 'cities' => ['ULAN BATOR']],
                'MYANMAR (BIRMANIA)' => ['code' => 'MMR', 'cities' => ['NAYPYIDAW']],
                'NEPAL' => ['code' => 'NPL', 'cities' => ['KATMANDÚ']],
                'OMÁN' => ['code' => 'OMN', 'cities' => ['MASCATE']],
                'PAKISTÁN' => ['code' => 'PAK', 'cities' => ['ISLAMABAD']],
                'PALESTINA' => ['code' => 'PSE', 'cities' => ['RAMALA']],
                'SINGAPUR' => ['code' => 'SGP', 'cities' => ['SINGAPUR']],
                'SIRIA' => ['code' => 'SYR', 'cities' => ['DAMASCO']],
                'SRI LANKA' => ['code' => 'LKA', 'cities' => ['COLOMBO']],
                'TAILANDIA' => ['code' => 'THA', 'cities' => ['BANGKOK']],
                'TAIWÁN' => ['code' => 'TWN', 'cities' => ['TAIPÉI']],
                'TAYIKISTÁN' => ['code' => 'TJK', 'cities' => ['DUSAMBÉ']],
                'TIMOR ORIENTAL' => ['code' => 'TLS', 'cities' => ['DILI']],
                'TURKMENISTÁN' => ['code' => 'TKM', 'cities' => ['ASJABAD']],
                'TURQUÍA' => ['code' => 'TUR', 'cities' => ['ANKARA']],
                'UZBEKISTÁN' => ['code' => 'UZB', 'cities' => ['TASHKENT']],
                'VIETNAM' => ['code' => 'VNM', 'cities' => ['HANOI']],
                'YEMEN' => ['code' => 'YEM', 'cities' => ['SANÁ']],
            ],
            'EUROPA' => [
                'ALBANIA' => ['code' => 'ALB', 'cities' => ['TIRANA']],
                'ALEMANIA' => ['code' => 'DEU', 'cities' => ['BERLÍN']],
                'ANDORRA' => ['code' => 'AND', 'cities' => ['ANDORRA LA VIEJA']],
                'ARMENIA' => ['code' => 'ARM', 'cities' => ['EREVÁN']],
                'AUSTRIA' => ['code' => 'AUT', 'cities' => ['VIENA']],
                'AZERBAIYÁN' => ['code' => 'AZE', 'cities' => ['BAKÚ']],
                'BÉLGICA' => ['code' => 'BEL', 'cities' => ['BRUSELAS']],
                'BIELORRUSIA' => ['code' => 'BLR', 'cities' => ['MINSK']],
                'BOSNIA Y HERZEGOVINA' => ['code' => 'BIH', 'cities' => ['SARAJEVO']],
                'BULGARIA' => ['code' => 'BGR', 'cities' => ['SOFÍA']],
                'CROACIA' => ['code' => 'HRV', 'cities' => ['ZAGREB']],
                'DINAMARCA' => ['code' => 'DNK', 'cities' => ['COPENHAGUE']],
                'ESLOVAQUIA' => ['code' => 'SVK', 'cities' => ['BRATISLAVA']],
                'ESLOVENIA' => ['code' => 'SVN', 'cities' => ['LUBLIJANA']],
                'ESPAÑA' => ['code' => 'ESP', 'cities' => ['MADRID']],
                'ESTONIA' => ['code' => 'EST', 'cities' => ['TALLÍN']],
                'FINLANDIA' => ['code' => 'FIN', 'cities' => ['HELSINKI']],
                'FRANCIA' => ['code' => 'FRA', 'cities' => ['PARÍS']],
                'GEORGIA' => ['code' => 'GEO', 'cities' => ['TIFLIS']],
                'GRECIA' => ['code' => 'GRC', 'cities' => ['ATENAS']],
                'HUNGRÍA' => ['code' => 'HUN', 'cities' => ['BUDAPEST']],
                'IRLANDA' => ['code' => 'IRL', 'cities' => ['DUBLÍN']],
                'ISLANDIA' => ['code' => 'ISL', 'cities' => ['REIKIAVIK']],
                'ITALIA' => ['code' => 'ITA', 'cities' => ['ROMA']],
                'LETONIA' => ['code' => 'LVA', 'cities' => ['RIGA']],
                'LIECHTENSTEIN' => ['code' => 'LIE', 'cities' => ['VADUZ']],
                'LITUANIA' => ['code' => 'LTU', 'cities' => ['VILNA']],
                'LUXEMBURGO' => ['code' => 'LUX', 'cities' => ['LUXEMBURGO']],
                'MACEDONIA DEL NORTE' => ['code' => 'MKD', 'cities' => ['SKOPJE']],
                'MALTA' => ['code' => 'MLT', 'cities' => ['LA VALETA']],
                'MOLDAVIA' => ['code' => 'MDA', 'cities' => ['CHISINAU']],
                'MÓNACO' => ['code' => 'MCO', 'cities' => ['MÓNACO']],
                'MONTENEGRO' => ['code' => 'MNE', 'cities' => ['PODGORICA']],
                'NORUEGA' => ['code' => 'NOR', 'cities' => ['OSLO']],
                'PAÍSES BAJOS' => ['code' => 'NLD', 'cities' => ['AMSTERDAM']],
                'POLONIA' => ['code' => 'POL', 'cities' => ['VARSOVIA']],
                'PORTUGAL' => ['code' => 'PRT', 'cities' => ['LISBOA']],
                'REINO UNIDO' => ['code' => 'GBR', 'cities' => ['LONDRES']],
                'REPÚBLICA CHECA' => ['code' => 'CZE', 'cities' => ['PRAGA']],
                'RUMANIA' => ['code' => 'ROU', 'cities' => ['BUCAREST']],
                'RUSIA' => ['code' => 'RUS', 'cities' => ['MOSCÚ']],
                'SAN MARINO' => ['code' => 'SMR', 'cities' => ['CIUDAD DE SAN MARINO']],
                'SERBIA' => ['code' => 'SRB', 'cities' => ['BELGRADO']],
                'SUECIA' => ['code' => 'SWE', 'cities' => ['ESTOCOLMO']],
                'SUIZA' => ['code' => 'CHE', 'cities' => ['BERNA']],
                'UCRANIA' => ['code' => 'UKR', 'cities' => ['KIEV']],
                'VATICANO' => ['code' => 'VAT', 'cities' => ['CIUDAD DEL VATICANO']],
            ],
            'OCEANÍA' => [
                'AUSTRALIA' => ['code' => 'AUS', 'cities' => ['CANBERRA']],
                'FIYI' => ['code' => 'FJI', 'cities' => ['SUVA']],
                'ISLAS MARSHALL' => ['code' => 'MHL', 'cities' => ['MAJURO']],
                'ISLAS SALOMÓN' => ['code' => 'SLB', 'cities' => ['HONIARA']],
                'KIRIBATI' => ['code' => 'KIR', 'cities' => ['TARAWA']],
                'MICRONESIA' => ['code' => 'FSM', 'cities' => ['PALIKIR']],
                'NAURU' => ['code' => 'NRU', 'cities' => ['YAREN']],
                'NUEVA ZELANDA' => ['code' => 'NZL', 'cities' => ['WELLINGTON']],
                'PALAOS' => ['code' => 'PLW', 'cities' => ['MELEKEOK']],
                'PAPÚA NUEVA GUINEA' => ['code' => 'PNG', 'cities' => ['PORT MORESBY']],
                'SAMOA' => ['code' => 'WSM', 'cities' => ['APIA']],
                'TONGA' => ['code' => 'TON', 'cities' => ['NUKU\'ALOFA']],
                'TUVALU' => ['code' => 'TUV', 'cities' => ['FUNAFUTI']],
                'VANUATU' => ['code' => 'VUT', 'cities' => ['PORT VILA']],
            ],
        ];


         // Insertar países y ciudades
         foreach ($countriesData as $continentName => $countries) {
            $continentId = $continentIds[$continentName];

            foreach ($countries as $countryName => $countryData) {
                // Insertar país
                $countryId = DB::table('countries')->insertGetId([
                    'name' => $countryName,
                    'code' => $countryData['code'],
                    'continent_id' => $continentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insertar ciudades
                foreach ($countryData['cities'] as $cityName) {
                    DB::table('cities')->insert([
                        'name' => $cityName,
                        'country_id' => $countryId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

    }
}
