<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Response
 * @package lying\service
 */
class Response extends Service
{
    /**
     * @var array 常见的HTTP状态码
     */
    private static $_httpCode = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var array 常用的文件后缀对应的MIME
     */
    private static $_mimeTypes = [
        '3dml' => 'text/vnd.in3d.3dml',
        '3ds' => 'image/x-3ds',
        '3g2' => 'video/3gpp2',
        '3gp' => 'video/3gpp',
        '7z' => 'application/x-7z-compressed',
        'aab' => 'application/x-authorware-bin',
        'aac' => 'audio/x-aac',
        'aam' => 'application/x-authorware-map',
        'aas' => 'application/x-authorware-seg',
        'abw' => 'application/x-abiword',
        'ac' => 'application/pkix-attr-cert',
        'acc' => 'application/vnd.americandynamics.acc',
        'ace' => 'application/x-ace-compressed',
        'acu' => 'application/vnd.acucobol',
        'acutc' => 'application/vnd.acucorp',
        'adp' => 'audio/adpcm',
        'aep' => 'application/vnd.audiograph',
        'afm' => 'application/x-font-type1',
        'afp' => 'application/vnd.ibm.modcap',
        'ahead' => 'application/vnd.ahead.space',
        'ai' => 'application/postscript',
        'aif' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'air' => 'application/vnd.adobe.air-application-installer-package+zip',
        'ait' => 'application/vnd.dvb.ait',
        'ami' => 'application/vnd.amiga.ami',
        'apk' => 'application/vnd.android.package-archive',
        'appcache' => 'text/cache-manifest',
        'application' => 'application/x-ms-application',
        'apr' => 'application/vnd.lotus-approach',
        'arc' => 'application/x-freearc',
        'asc' => 'application/pgp-signature',
        'asf' => 'video/x-ms-asf',
        'asm' => 'text/x-asm',
        'aso' => 'application/vnd.accpac.simply.aso',
        'asx' => 'video/x-ms-asf',
        'atc' => 'application/vnd.acucorp',
        'atom' => 'application/atom+xml',
        'atomcat' => 'application/atomcat+xml',
        'atomsvc' => 'application/atomsvc+xml',
        'atx' => 'application/vnd.antix.game-component',
        'au' => 'audio/basic',
        'avi' => 'video/x-msvideo',
        'aw' => 'application/applixware',
        'azf' => 'application/vnd.airzip.filesecure.azf',
        'azs' => 'application/vnd.airzip.filesecure.azs',
        'azw' => 'application/vnd.amazon.ebook',
        'bat' => 'application/x-msdownload',
        'bcpio' => 'application/x-bcpio',
        'bdf' => 'application/x-font-bdf',
        'bdm' => 'application/vnd.syncml.dm+wbxml',
        'bed' => 'application/vnd.realvnc.bed',
        'bh2' => 'application/vnd.fujitsu.oasysprs',
        'bin' => 'application/octet-stream',
        'blb' => 'application/x-blorb',
        'blorb' => 'application/x-blorb',
        'bmi' => 'application/vnd.bmi',
        'bmp' => 'image/bmp',
        'book' => 'application/vnd.framemaker',
        'box' => 'application/vnd.previewsystems.box',
        'boz' => 'application/x-bzip2',
        'bpk' => 'application/octet-stream',
        'btif' => 'image/prs.btif',
        'bz' => 'application/x-bzip',
        'bz2' => 'application/x-bzip2',
        'c' => 'text/x-c',
        'c11amc' => 'application/vnd.cluetrust.cartomobile-config',
        'c11amz' => 'application/vnd.cluetrust.cartomobile-config-pkg',
        'c4d' => 'application/vnd.clonk.c4group',
        'c4f' => 'application/vnd.clonk.c4group',
        'c4g' => 'application/vnd.clonk.c4group',
        'c4p' => 'application/vnd.clonk.c4group',
        'c4u' => 'application/vnd.clonk.c4group',
        'cab' => 'application/vnd.ms-cab-compressed',
        'caf' => 'audio/x-caf',
        'cap' => 'application/vnd.tcpdump.pcap',
        'car' => 'application/vnd.curl.car',
        'cat' => 'application/vnd.ms-pki.seccat',
        'cb7' => 'application/x-cbr',
        'cba' => 'application/x-cbr',
        'cbr' => 'application/x-cbr',
        'cbt' => 'application/x-cbr',
        'cbz' => 'application/x-cbr',
        'cc' => 'text/x-c',
        'cct' => 'application/x-director',
        'ccxml' => 'application/ccxml+xml',
        'cdbcmsg' => 'application/vnd.contact.cmsg',
        'cdf' => 'application/x-netcdf',
        'cdkey' => 'application/vnd.mediastation.cdkey',
        'cdmia' => 'application/cdmi-capability',
        'cdmic' => 'application/cdmi-container',
        'cdmid' => 'application/cdmi-domain',
        'cdmio' => 'application/cdmi-object',
        'cdmiq' => 'application/cdmi-queue',
        'cdx' => 'chemical/x-cdx',
        'cdxml' => 'application/vnd.chemdraw+xml',
        'cdy' => 'application/vnd.cinderella',
        'cer' => 'application/pkix-cert',
        'cfs' => 'application/x-cfs-compressed',
        'cgm' => 'image/cgm',
        'chat' => 'application/x-chat',
        'chm' => 'application/vnd.ms-htmlhelp',
        'chrt' => 'application/vnd.kde.kchart',
        'cif' => 'chemical/x-cif',
        'cii' => 'application/vnd.anser-web-certificate-issue-initiation',
        'cil' => 'application/vnd.ms-artgalry',
        'cla' => 'application/vnd.claymore',
        'class' => 'application/java-vm',
        'clkk' => 'application/vnd.crick.clicker.keyboard',
        'clkp' => 'application/vnd.crick.clicker.palette',
        'clkt' => 'application/vnd.crick.clicker.template',
        'clkw' => 'application/vnd.crick.clicker.wordbank',
        'clkx' => 'application/vnd.crick.clicker',
        'clp' => 'application/x-msclip',
        'cmc' => 'application/vnd.cosmocaller',
        'cmdf' => 'chemical/x-cmdf',
        'cml' => 'chemical/x-cml',
        'cmp' => 'application/vnd.yellowriver-custom-menu',
        'cmx' => 'image/x-cmx',
        'cod' => 'application/vnd.rim.cod',
        'com' => 'application/x-msdownload',
        'conf' => 'text/plain',
        'cpio' => 'application/x-cpio',
        'cpp' => 'text/x-c',
        'cpt' => 'application/mac-compactpro',
        'crd' => 'application/x-mscardfile',
        'crl' => 'application/pkix-crl',
        'crt' => 'application/x-x509-ca-cert',
        'cryptonote' => 'application/vnd.rig.cryptonote',
        'csh' => 'application/x-csh',
        'csml' => 'chemical/x-csml',
        'csp' => 'application/vnd.commonspace',
        'css' => 'text/css',
        'cst' => 'application/x-director',
        'csv' => 'text/csv',
        'cu' => 'application/cu-seeme',
        'curl' => 'text/vnd.curl',
        'cww' => 'application/prs.cww',
        'cxt' => 'application/x-director',
        'cxx' => 'text/x-c',
        'dae' => 'model/vnd.collada+xml',
        'daf' => 'application/vnd.mobius.daf',
        'dart' => 'application/vnd.dart',
        'dataless' => 'application/vnd.fdsn.seed',
        'davmount' => 'application/davmount+xml',
        'dbk' => 'application/docbook+xml',
        'dcr' => 'application/x-director',
        'dcurl' => 'text/vnd.curl.dcurl',
        'dd2' => 'application/vnd.oma.dd2+xml',
        'ddd' => 'application/vnd.fujixerox.ddd',
        'deb' => 'application/x-debian-package',
        'def' => 'text/plain',
        'deploy' => 'application/octet-stream',
        'der' => 'application/x-x509-ca-cert',
        'dfac' => 'application/vnd.dreamfactory',
        'dgc' => 'application/x-dgc-compressed',
        'dic' => 'text/x-c',
        'dir' => 'application/x-director',
        'dis' => 'application/vnd.mobius.dis',
        'dist' => 'application/octet-stream',
        'distz' => 'application/octet-stream',
        'djv' => 'image/vnd.djvu',
        'djvu' => 'image/vnd.djvu',
        'dll' => 'application/x-msdownload',
        'dmg' => 'application/x-apple-diskimage',
        'dmp' => 'application/vnd.tcpdump.pcap',
        'dms' => 'application/octet-stream',
        'dna' => 'application/vnd.dna',
        'doc' => 'application/msword',
        'docm' => 'application/vnd.ms-word.document.macroenabled.12',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dot' => 'application/msword',
        'dotm' => 'application/vnd.ms-word.template.macroenabled.12',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dp' => 'application/vnd.osgi.dp',
        'dpg' => 'application/vnd.dpgraph',
        'dra' => 'audio/vnd.dra',
        'dsc' => 'text/prs.lines.tag',
        'dssc' => 'application/dssc+der',
        'dtb' => 'application/x-dtbook+xml',
        'dtd' => 'application/xml-dtd',
        'dts' => 'audio/vnd.dts',
        'dtshd' => 'audio/vnd.dts.hd',
        'dump' => 'application/octet-stream',
        'dvb' => 'video/vnd.dvb.file',
        'dvi' => 'application/x-dvi',
        'dwf' => 'model/vnd.dwf',
        'dwg' => 'image/vnd.dwg',
        'dxf' => 'image/vnd.dxf',
        'dxp' => 'application/vnd.spotfire.dxp',
        'dxr' => 'application/x-director',
        'ecelp4800' => 'audio/vnd.nuera.ecelp4800',
        'ecelp7470' => 'audio/vnd.nuera.ecelp7470',
        'ecelp9600' => 'audio/vnd.nuera.ecelp9600',
        'ecma' => 'application/ecmascript',
        'edm' => 'application/vnd.novadigm.edm',
        'edx' => 'application/vnd.novadigm.edx',
        'efif' => 'application/vnd.picsel',
        'ei6' => 'application/vnd.pg.osasli',
        'elc' => 'application/octet-stream',
        'emf' => 'application/x-msmetafile',
        'eml' => 'message/rfc822',
        'emma' => 'application/emma+xml',
        'emz' => 'application/x-msmetafile',
        'eol' => 'audio/vnd.digital-winds',
        'eot' => 'application/vnd.ms-fontobject',
        'eps' => 'application/postscript',
        'epub' => 'application/epub+zip',
        'es3' => 'application/vnd.eszigno3+xml',
        'esa' => 'application/vnd.osgi.subsystem',
        'esf' => 'application/vnd.epson.esf',
        'et3' => 'application/vnd.eszigno3+xml',
        'etx' => 'text/x-setext',
        'eva' => 'application/x-eva',
        'evy' => 'application/x-envoy',
        'exe' => 'application/x-msdownload',
        'exi' => 'application/exi',
        'ext' => 'application/vnd.novadigm.ext',
        'ez' => 'application/andrew-inset',
        'ez2' => 'application/vnd.ezpix-album',
        'ez3' => 'application/vnd.ezpix-package',
        'f' => 'text/x-fortran',
        'f4v' => 'video/x-f4v',
        'f77' => 'text/x-fortran',
        'f90' => 'text/x-fortran',
        'fbs' => 'image/vnd.fastbidsheet',
        'fcdt' => 'application/vnd.adobe.formscentral.fcdt',
        'fcs' => 'application/vnd.isac.fcs',
        'fdf' => 'application/vnd.fdf',
        'fe_launch' => 'application/vnd.denovo.fcselayout-link',
        'fg5' => 'application/vnd.fujitsu.oasysgp',
        'fgd' => 'application/x-director',
        'fh' => 'image/x-freehand',
        'fh4' => 'image/x-freehand',
        'fh5' => 'image/x-freehand',
        'fh7' => 'image/x-freehand',
        'fhc' => 'image/x-freehand',
        'fig' => 'application/x-xfig',
        'flac' => 'audio/x-flac',
        'fli' => 'video/x-fli',
        'flo' => 'application/vnd.micrografx.flo',
        'flv' => 'video/x-flv',
        'flw' => 'application/vnd.kde.kivio',
        'flx' => 'text/vnd.fmi.flexstor',
        'fly' => 'text/vnd.fly',
        'fm' => 'application/vnd.framemaker',
        'fnc' => 'application/vnd.frogans.fnc',
        'for' => 'text/x-fortran',
        'fpx' => 'image/vnd.fpx',
        'frame' => 'application/vnd.framemaker',
        'fsc' => 'application/vnd.fsc.weblaunch',
        'fst' => 'image/vnd.fst',
        'ftc' => 'application/vnd.fluxtime.clip',
        'fti' => 'application/vnd.anser-web-funds-transfer-initiation',
        'fvt' => 'video/vnd.fvt',
        'fxp' => 'application/vnd.adobe.fxp',
        'fxpl' => 'application/vnd.adobe.fxp',
        'fzs' => 'application/vnd.fuzzysheet',
        'g2w' => 'application/vnd.geoplan',
        'g3' => 'image/g3fax',
        'g3w' => 'application/vnd.geospace',
        'gac' => 'application/vnd.groove-account',
        'gam' => 'application/x-tads',
        'gbr' => 'application/rpki-ghostbusters',
        'gca' => 'application/x-gca-compressed',
        'gdl' => 'model/vnd.gdl',
        'geo' => 'application/vnd.dynageo',
        'gex' => 'application/vnd.geometry-explorer',
        'ggb' => 'application/vnd.geogebra.file',
        'ggt' => 'application/vnd.geogebra.tool',
        'ghf' => 'application/vnd.groove-help',
        'gif' => 'image/gif',
        'gim' => 'application/vnd.groove-identity-message',
        'gml' => 'application/gml+xml',
        'gmx' => 'application/vnd.gmx',
        'gnumeric' => 'application/x-gnumeric',
        'gph' => 'application/vnd.flographit',
        'gpx' => 'application/gpx+xml',
        'gqf' => 'application/vnd.grafeq',
        'gqs' => 'application/vnd.grafeq',
        'gram' => 'application/srgs',
        'gramps' => 'application/x-gramps-xml',
        'gre' => 'application/vnd.geometry-explorer',
        'grv' => 'application/vnd.groove-injector',
        'grxml' => 'application/srgs+xml',
        'gsf' => 'application/x-font-ghostscript',
        'gtar' => 'application/x-gtar',
        'gtm' => 'application/vnd.groove-tool-message',
        'gtw' => 'model/vnd.gtw',
        'gv' => 'text/vnd.graphviz',
        'gxf' => 'application/gxf',
        'gxt' => 'application/vnd.geonext',
        'h' => 'text/x-c',
        'h261' => 'video/h261',
        'h263' => 'video/h263',
        'h264' => 'video/h264',
        'hal' => 'application/vnd.hal+xml',
        'hbci' => 'application/vnd.hbci',
        'hdf' => 'application/x-hdf',
        'hh' => 'text/x-c',
        'hlp' => 'application/winhlp',
        'hpgl' => 'application/vnd.hp-hpgl',
        'hpid' => 'application/vnd.hp-hpid',
        'hps' => 'application/vnd.hp-hps',
        'hqx' => 'application/mac-binhex40',
        'htke' => 'application/vnd.kenameaapp',
        'htm' => 'text/html',
        'html' => 'text/html',
        'hvd' => 'application/vnd.yamaha.hv-dic',
        'hvp' => 'application/vnd.yamaha.hv-voice',
        'hvs' => 'application/vnd.yamaha.hv-script',
        'i2g' => 'application/vnd.intergeo',
        'icc' => 'application/vnd.iccprofile',
        'ice' => 'x-conference/x-cooltalk',
        'icm' => 'application/vnd.iccprofile',
        'ico' => 'image/x-icon',
        'ics' => 'text/calendar',
        'ief' => 'image/ief',
        'ifb' => 'text/calendar',
        'ifm' => 'application/vnd.shana.informed.formdata',
        'iges' => 'model/iges',
        'igl' => 'application/vnd.igloader',
        'igm' => 'application/vnd.insors.igm',
        'igs' => 'model/iges',
        'igx' => 'application/vnd.micrografx.igx',
        'iif' => 'application/vnd.shana.informed.interchange',
        'imp' => 'application/vnd.accpac.simply.imp',
        'ims' => 'application/vnd.ms-ims',
        'in' => 'text/plain',
        'ink' => 'application/inkml+xml',
        'inkml' => 'application/inkml+xml',
        'install' => 'application/x-install-instructions',
        'iota' => 'application/vnd.astraea-software.iota',
        'ipfix' => 'application/ipfix',
        'ipk' => 'application/vnd.shana.informed.package',
        'irm' => 'application/vnd.ibm.rights-management',
        'irp' => 'application/vnd.irepository.package+xml',
        'iso' => 'application/x-iso9660-image',
        'itp' => 'application/vnd.shana.informed.formtemplate',
        'ivp' => 'application/vnd.immervision-ivp',
        'ivu' => 'application/vnd.immervision-ivu',
        'jad' => 'text/vnd.sun.j2me.app-descriptor',
        'jam' => 'application/vnd.jam',
        'jar' => 'application/java-archive',
        'java' => 'text/x-java-source',
        'jisp' => 'application/vnd.jisp',
        'jlt' => 'application/vnd.hp-jlyt',
        'jnlp' => 'application/x-java-jnlp-file',
        'joda' => 'application/vnd.joost.joda-archive',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpgm' => 'video/jpm',
        'jpgv' => 'video/jpeg',
        'jpm' => 'video/jpm',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'jsonml' => 'application/jsonml+json',
        'kar' => 'audio/midi',
        'karbon' => 'application/vnd.kde.karbon',
        'kfo' => 'application/vnd.kde.kformula',
        'kia' => 'application/vnd.kidspiration',
        'kml' => 'application/vnd.google-earth.kml+xml',
        'kmz' => 'application/vnd.google-earth.kmz',
        'kne' => 'application/vnd.kinar',
        'knp' => 'application/vnd.kinar',
        'kon' => 'application/vnd.kde.kontour',
        'kpr' => 'application/vnd.kde.kpresenter',
        'kpt' => 'application/vnd.kde.kpresenter',
        'kpxx' => 'application/vnd.ds-keypoint',
        'ksp' => 'application/vnd.kde.kspread',
        'ktr' => 'application/vnd.kahootz',
        'ktx' => 'image/ktx',
        'ktz' => 'application/vnd.kahootz',
        'kwd' => 'application/vnd.kde.kword',
        'kwt' => 'application/vnd.kde.kword',
        'lasxml' => 'application/vnd.las.las+xml',
        'latex' => 'application/x-latex',
        'lbd' => 'application/vnd.llamagraphics.life-balance.desktop',
        'lbe' => 'application/vnd.llamagraphics.life-balance.exchange+xml',
        'les' => 'application/vnd.hhe.lesson-player',
        'lha' => 'application/x-lzh-compressed',
        'link66' => 'application/vnd.route66.link66+xml',
        'list' => 'text/plain',
        'list3820' => 'application/vnd.ibm.modcap',
        'listafp' => 'application/vnd.ibm.modcap',
        'lnk' => 'application/x-ms-shortcut',
        'log' => 'text/plain',
        'lostxml' => 'application/lost+xml',
        'lrf' => 'application/octet-stream',
        'lrm' => 'application/vnd.ms-lrm',
        'ltf' => 'application/vnd.frogans.ltf',
        'lvp' => 'audio/vnd.lucent.voice',
        'lwp' => 'application/vnd.lotus-wordpro',
        'lzh' => 'application/x-lzh-compressed',
        'm13' => 'application/x-msmediaview',
        'm14' => 'application/x-msmediaview',
        'm1v' => 'video/mpeg',
        'm21' => 'application/mp21',
        'm2a' => 'audio/mpeg',
        'm2v' => 'video/mpeg',
        'm3a' => 'audio/mpeg',
        'm3u' => 'audio/x-mpegurl',
        'm3u8' => 'application/vnd.apple.mpegurl',
        'm4a' => 'audio/mp4',
        'm4u' => 'video/vnd.mpegurl',
        'm4v' => 'video/x-m4v',
        'ma' => 'application/mathematica',
        'mads' => 'application/mads+xml',
        'mag' => 'application/vnd.ecowin.chart',
        'maker' => 'application/vnd.framemaker',
        'man' => 'text/troff',
        'mar' => 'application/octet-stream',
        'mathml' => 'application/mathml+xml',
        'mb' => 'application/mathematica',
        'mbk' => 'application/vnd.mobius.mbk',
        'mbox' => 'application/mbox',
        'mc1' => 'application/vnd.medcalcdata',
        'mcd' => 'application/vnd.mcd',
        'mcurl' => 'text/vnd.curl.mcurl',
        'mdb' => 'application/x-msaccess',
        'mdi' => 'image/vnd.ms-modi',
        'me' => 'text/troff',
        'mesh' => 'model/mesh',
        'meta4' => 'application/metalink4+xml',
        'metalink' => 'application/metalink+xml',
        'mets' => 'application/mets+xml',
        'mfm' => 'application/vnd.mfmp',
        'mft' => 'application/rpki-manifest',
        'mgp' => 'application/vnd.osgeo.mapguide.package',
        'mgz' => 'application/vnd.proteus.magazine',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mie' => 'application/x-mie',
        'mif' => 'application/vnd.mif',
        'mime' => 'message/rfc822',
        'mj2' => 'video/mj2',
        'mjp2' => 'video/mj2',
        'mk3d' => 'video/x-matroska',
        'mka' => 'audio/x-matroska',
        'mks' => 'video/x-matroska',
        'mkv' => 'video/x-matroska',
        'mlp' => 'application/vnd.dolby.mlp',
        'mmd' => 'application/vnd.chipnuts.karaoke-mmd',
        'mmf' => 'application/vnd.smaf',
        'mmr' => 'image/vnd.fujixerox.edmics-mmr',
        'mng' => 'video/x-mng',
        'mny' => 'application/x-msmoney',
        'mobi' => 'application/x-mobipocket-ebook',
        'mods' => 'application/mods+xml',
        'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mp2' => 'audio/mpeg',
        'mp21' => 'application/mp21',
        'mp2a' => 'audio/mpeg',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'mp4a' => 'audio/mp4',
        'mp4s' => 'application/mp4',
        'mp4v' => 'video/mp4',
        'mpc' => 'application/vnd.mophun.certificate',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpg4' => 'video/mp4',
        'mpga' => 'audio/mpeg',
        'mpkg' => 'application/vnd.apple.installer+xml',
        'mpm' => 'application/vnd.blueice.multipass',
        'mpn' => 'application/vnd.mophun.application',
        'mpp' => 'application/vnd.ms-project',
        'mpt' => 'application/vnd.ms-project',
        'mpy' => 'application/vnd.ibm.minipay',
        'mqy' => 'application/vnd.mobius.mqy',
        'mrc' => 'application/marc',
        'mrcx' => 'application/marcxml+xml',
        'ms' => 'text/troff',
        'mscml' => 'application/mediaservercontrol+xml',
        'mseed' => 'application/vnd.fdsn.mseed',
        'mseq' => 'application/vnd.mseq',
        'msf' => 'application/vnd.epson.msf',
        'msh' => 'model/mesh',
        'msi' => 'application/x-msdownload',
        'msl' => 'application/vnd.mobius.msl',
        'msty' => 'application/vnd.muvee.style',
        'mts' => 'model/vnd.mts',
        'mus' => 'application/vnd.musician',
        'musicxml' => 'application/vnd.recordare.musicxml+xml',
        'mvb' => 'application/x-msmediaview',
        'mwf' => 'application/vnd.mfer',
        'mxf' => 'application/mxf',
        'mxl' => 'application/vnd.recordare.musicxml',
        'mxml' => 'application/xv+xml',
        'mxs' => 'application/vnd.triscape.mxs',
        'mxu' => 'video/vnd.mpegurl',
        'n-gage' => 'application/vnd.nokia.n-gage.symbian.install',
        'n3' => 'text/n3',
        'nb' => 'application/mathematica',
        'nbp' => 'application/vnd.wolfram.player',
        'nc' => 'application/x-netcdf',
        'ncx' => 'application/x-dtbncx+xml',
        'nfo' => 'text/x-nfo',
        'ngdat' => 'application/vnd.nokia.n-gage.data',
        'nitf' => 'application/vnd.nitf',
        'nlu' => 'application/vnd.neurolanguage.nlu',
        'nml' => 'application/vnd.enliven',
        'nnd' => 'application/vnd.noblenet-directory',
        'nns' => 'application/vnd.noblenet-sealer',
        'nnw' => 'application/vnd.noblenet-web',
        'npx' => 'image/vnd.net-fpx',
        'nsc' => 'application/x-conference',
        'nsf' => 'application/vnd.lotus-notes',
        'ntf' => 'application/vnd.nitf',
        'nzb' => 'application/x-nzb',
        'oa2' => 'application/vnd.fujitsu.oasys2',
        'oa3' => 'application/vnd.fujitsu.oasys3',
        'oas' => 'application/vnd.fujitsu.oasys',
        'obd' => 'application/x-msbinder',
        'obj' => 'application/x-tgif',
        'oda' => 'application/oda',
        'odb' => 'application/vnd.oasis.opendocument.database',
        'odc' => 'application/vnd.oasis.opendocument.chart',
        'odf' => 'application/vnd.oasis.opendocument.formula',
        'odft' => 'application/vnd.oasis.opendocument.formula-template',
        'odg' => 'application/vnd.oasis.opendocument.graphics',
        'odi' => 'application/vnd.oasis.opendocument.image',
        'odm' => 'application/vnd.oasis.opendocument.text-master',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'oga' => 'audio/ogg',
        'ogg' => 'audio/ogg',
        'ogv' => 'video/ogg',
        'ogx' => 'application/ogg',
        'omdoc' => 'application/omdoc+xml',
        'onepkg' => 'application/onenote',
        'onetmp' => 'application/onenote',
        'onetoc' => 'application/onenote',
        'onetoc2' => 'application/onenote',
        'opf' => 'application/oebps-package+xml',
        'opml' => 'text/x-opml',
        'oprc' => 'application/vnd.palm',
        'org' => 'application/vnd.lotus-organizer',
        'osf' => 'application/vnd.yamaha.openscoreformat',
        'osfpvg' => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
        'otc' => 'application/vnd.oasis.opendocument.chart-template',
        'otf' => 'font/otf',
        'otg' => 'application/vnd.oasis.opendocument.graphics-template',
        'oth' => 'application/vnd.oasis.opendocument.text-web',
        'oti' => 'application/vnd.oasis.opendocument.image-template',
        'otp' => 'application/vnd.oasis.opendocument.presentation-template',
        'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'ott' => 'application/vnd.oasis.opendocument.text-template',
        'oxps' => 'application/oxps',
        'oxt' => 'application/vnd.openofficeorg.extension',
        'p' => 'text/x-pascal',
        'p10' => 'application/pkcs10',
        'p12' => 'application/x-pkcs12',
        'p7b' => 'application/x-pkcs7-certificates',
        'p7c' => 'application/pkcs7-mime',
        'p7m' => 'application/pkcs7-mime',
        'p7r' => 'application/x-pkcs7-certreqresp',
        'p7s' => 'application/pkcs7-signature',
        'p8' => 'application/pkcs8',
        'pas' => 'text/x-pascal',
        'paw' => 'application/vnd.pawaafile',
        'pbd' => 'application/vnd.powerbuilder6',
        'pbm' => 'image/x-portable-bitmap',
        'pcap' => 'application/vnd.tcpdump.pcap',
        'pcf' => 'application/x-font-pcf',
        'pcl' => 'application/vnd.hp-pcl',
        'pclxl' => 'application/vnd.hp-pclxl',
        'pct' => 'image/x-pict',
        'pcurl' => 'application/vnd.curl.pcurl',
        'pcx' => 'image/x-pcx',
        'pdb' => 'application/vnd.palm',
        'pdf' => 'application/pdf',
        'pfa' => 'application/x-font-type1',
        'pfb' => 'application/x-font-type1',
        'pfm' => 'application/x-font-type1',
        'pfr' => 'application/font-tdpfr',
        'pfx' => 'application/x-pkcs12',
        'pgm' => 'image/x-portable-graymap',
        'pgn' => 'application/x-chess-pgn',
        'pgp' => 'application/pgp-encrypted',
        'pic' => 'image/x-pict',
        'pkg' => 'application/octet-stream',
        'pki' => 'application/pkixcmp',
        'pkipath' => 'application/pkix-pkipath',
        'plb' => 'application/vnd.3gpp.pic-bw-large',
        'plc' => 'application/vnd.mobius.plc',
        'plf' => 'application/vnd.pocketlearn',
        'pls' => 'application/pls+xml',
        'pml' => 'application/vnd.ctc-posml',
        'png' => 'image/png',
        'pnm' => 'image/x-portable-anymap',
        'portpkg' => 'application/vnd.macports.portpkg',
        'pot' => 'application/vnd.ms-powerpoint',
        'potm' => 'application/vnd.ms-powerpoint.template.macroenabled.12',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'ppam' => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
        'ppd' => 'application/vnd.cups-ppd',
        'ppm' => 'image/x-portable-pixmap',
        'pps' => 'application/vnd.ms-powerpoint',
        'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptm' => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pqa' => 'application/vnd.palm',
        'prc' => 'application/x-mobipocket-ebook',
        'pre' => 'application/vnd.lotus-freelance',
        'prf' => 'application/pics-rules',
        'ps' => 'application/postscript',
        'psb' => 'application/vnd.3gpp.pic-bw-small',
        'psd' => 'image/vnd.adobe.photoshop',
        'psf' => 'application/x-font-linux-psf',
        'pskcxml' => 'application/pskc+xml',
        'ptid' => 'application/vnd.pvi.ptid1',
        'pub' => 'application/x-mspublisher',
        'pvb' => 'application/vnd.3gpp.pic-bw-var',
        'pwn' => 'application/vnd.3m.post-it-notes',
        'pya' => 'audio/vnd.ms-playready.media.pya',
        'pyv' => 'video/vnd.ms-playready.media.pyv',
        'qam' => 'application/vnd.epson.quickanime',
        'qbo' => 'application/vnd.intu.qbo',
        'qfx' => 'application/vnd.intu.qfx',
        'qps' => 'application/vnd.publishare-delta-tree',
        'qt' => 'video/quicktime',
        'qwd' => 'application/vnd.quark.quarkxpress',
        'qwt' => 'application/vnd.quark.quarkxpress',
        'qxb' => 'application/vnd.quark.quarkxpress',
        'qxd' => 'application/vnd.quark.quarkxpress',
        'qxl' => 'application/vnd.quark.quarkxpress',
        'qxt' => 'application/vnd.quark.quarkxpress',
        'ra' => 'audio/x-pn-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'rar' => 'application/x-rar-compressed',
        'ras' => 'image/x-cmu-raster',
        'rcprofile' => 'application/vnd.ipunplugged.rcprofile',
        'rdf' => 'application/rdf+xml',
        'rdz' => 'application/vnd.data-vision.rdz',
        'rep' => 'application/vnd.businessobjects',
        'res' => 'application/x-dtbresource+xml',
        'rgb' => 'image/x-rgb',
        'rif' => 'application/reginfo+xml',
        'rip' => 'audio/vnd.rip',
        'ris' => 'application/x-research-info-systems',
        'rl' => 'application/resource-lists+xml',
        'rlc' => 'image/vnd.fujixerox.edmics-rlc',
        'rld' => 'application/resource-lists-diff+xml',
        'rm' => 'application/vnd.rn-realmedia',
        'rmi' => 'audio/midi',
        'rmp' => 'audio/x-pn-realaudio-plugin',
        'rms' => 'application/vnd.jcp.javame.midlet-rms',
        'rmvb' => 'application/vnd.rn-realmedia-vbr',
        'rnc' => 'application/relax-ng-compact-syntax',
        'roa' => 'application/rpki-roa',
        'roff' => 'text/troff',
        'rp9' => 'application/vnd.cloanto.rp9',
        'rpss' => 'application/vnd.nokia.radio-presets',
        'rpst' => 'application/vnd.nokia.radio-preset',
        'rq' => 'application/sparql-query',
        'rs' => 'application/rls-services+xml',
        'rsd' => 'application/rsd+xml',
        'rss' => 'application/rss+xml',
        'rtf' => 'application/rtf',
        'rtx' => 'text/richtext',
        's' => 'text/x-asm',
        's3m' => 'audio/s3m',
        'saf' => 'application/vnd.yamaha.smaf-audio',
        'sbml' => 'application/sbml+xml',
        'sc' => 'application/vnd.ibm.secure-container',
        'scd' => 'application/x-msschedule',
        'scm' => 'application/vnd.lotus-screencam',
        'scq' => 'application/scvp-cv-request',
        'scs' => 'application/scvp-cv-response',
        'scurl' => 'text/vnd.curl.scurl',
        'sda' => 'application/vnd.stardivision.draw',
        'sdc' => 'application/vnd.stardivision.calc',
        'sdd' => 'application/vnd.stardivision.impress',
        'sdkd' => 'application/vnd.solent.sdkm+xml',
        'sdkm' => 'application/vnd.solent.sdkm+xml',
        'sdp' => 'application/sdp',
        'sdw' => 'application/vnd.stardivision.writer',
        'see' => 'application/vnd.seemail',
        'seed' => 'application/vnd.fdsn.seed',
        'sema' => 'application/vnd.sema',
        'semd' => 'application/vnd.semd',
        'semf' => 'application/vnd.semf',
        'ser' => 'application/java-serialized-object',
        'setpay' => 'application/set-payment-initiation',
        'setreg' => 'application/set-registration-initiation',
        'sfd-hdstx' => 'application/vnd.hydrostatix.sof-data',
        'sfs' => 'application/vnd.spotfire.sfs',
        'sfv' => 'text/x-sfv',
        'sgi' => 'image/sgi',
        'sgl' => 'application/vnd.stardivision.writer-global',
        'sgm' => 'text/sgml',
        'sgml' => 'text/sgml',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'shf' => 'application/shf+xml',
        'sid' => 'image/x-mrsid-image',
        'sig' => 'application/pgp-signature',
        'sil' => 'audio/silk',
        'silo' => 'model/mesh',
        'sis' => 'application/vnd.symbian.install',
        'sisx' => 'application/vnd.symbian.install',
        'sit' => 'application/x-stuffit',
        'sitx' => 'application/x-stuffitx',
        'skd' => 'application/vnd.koan',
        'skm' => 'application/vnd.koan',
        'skp' => 'application/vnd.koan',
        'skt' => 'application/vnd.koan',
        'sldm' => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
        'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'slt' => 'application/vnd.epson.salt',
        'sm' => 'application/vnd.stepmania.stepchart',
        'smf' => 'application/vnd.stardivision.math',
        'smi' => 'application/smil+xml',
        'smil' => 'application/smil+xml',
        'smv' => 'video/x-smv',
        'smzip' => 'application/vnd.stepmania.package',
        'snd' => 'audio/basic',
        'snf' => 'application/x-font-snf',
        'so' => 'application/octet-stream',
        'spc' => 'application/x-pkcs7-certificates',
        'spf' => 'application/vnd.yamaha.smaf-phrase',
        'spl' => 'application/x-futuresplash',
        'spot' => 'text/vnd.in3d.spot',
        'spp' => 'application/scvp-vp-response',
        'spq' => 'application/scvp-vp-request',
        'spx' => 'audio/ogg',
        'sql' => 'application/x-sql',
        'src' => 'application/x-wais-source',
        'srt' => 'application/x-subrip',
        'sru' => 'application/sru+xml',
        'srx' => 'application/sparql-results+xml',
        'ssdl' => 'application/ssdl+xml',
        'sse' => 'application/vnd.kodak-descriptor',
        'ssf' => 'application/vnd.epson.ssf',
        'ssml' => 'application/ssml+xml',
        'st' => 'application/vnd.sailingtracker.track',
        'stc' => 'application/vnd.sun.xml.calc.template',
        'std' => 'application/vnd.sun.xml.draw.template',
        'stf' => 'application/vnd.wt.stf',
        'sti' => 'application/vnd.sun.xml.impress.template',
        'stk' => 'application/hyperstudio',
        'stl' => 'application/vnd.ms-pki.stl',
        'str' => 'application/vnd.pg.format',
        'stw' => 'application/vnd.sun.xml.writer.template',
        'sub' => 'text/vnd.dvb.subtitle',
        'sus' => 'application/vnd.sus-calendar',
        'susp' => 'application/vnd.sus-calendar',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'svc' => 'application/vnd.dvb.service',
        'svd' => 'application/vnd.svd',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'swa' => 'application/x-director',
        'swf' => 'application/x-shockwave-flash',
        'swi' => 'application/vnd.aristanetworks.swi',
        'sxc' => 'application/vnd.sun.xml.calc',
        'sxd' => 'application/vnd.sun.xml.draw',
        'sxg' => 'application/vnd.sun.xml.writer.global',
        'sxi' => 'application/vnd.sun.xml.impress',
        'sxm' => 'application/vnd.sun.xml.math',
        'sxw' => 'application/vnd.sun.xml.writer',
        't' => 'text/troff',
        't3' => 'application/x-t3vm-image',
        'taglet' => 'application/vnd.mynfc',
        'tao' => 'application/vnd.tao.intent-module-archive',
        'tar' => 'application/x-tar',
        'tcap' => 'application/vnd.3gpp2.tcap',
        'tcl' => 'application/x-tcl',
        'teacher' => 'application/vnd.smart.teacher',
        'tei' => 'application/tei+xml',
        'teicorpus' => 'application/tei+xml',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'text' => 'text/plain',
        'tfi' => 'application/thraud+xml',
        'tfm' => 'application/x-tex-tfm',
        'tga' => 'image/x-tga',
        'thmx' => 'application/vnd.ms-officetheme',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'tmo' => 'application/vnd.tmobile-livetv',
        'torrent' => 'application/x-bittorrent',
        'tpl' => 'application/vnd.groove-tool-template',
        'tpt' => 'application/vnd.trid.tpt',
        'tr' => 'text/troff',
        'tra' => 'application/vnd.trueapp',
        'trm' => 'application/x-msterminal',
        'tsd' => 'application/timestamped-data',
        'tsv' => 'text/tab-separated-values',
        'ttc' => 'font/collection',
        'ttf' => 'font/ttf',
        'ttl' => 'text/turtle',
        'twd' => 'application/vnd.simtech-mindmapper',
        'twds' => 'application/vnd.simtech-mindmapper',
        'txd' => 'application/vnd.genomatix.tuxedo',
        'txf' => 'application/vnd.mobius.txf',
        'txt' => 'text/plain',
        'u32' => 'application/x-authorware-bin',
        'udeb' => 'application/x-debian-package',
        'ufd' => 'application/vnd.ufdl',
        'ufdl' => 'application/vnd.ufdl',
        'ulx' => 'application/x-glulx',
        'umj' => 'application/vnd.umajin',
        'unityweb' => 'application/vnd.unity',
        'uoml' => 'application/vnd.uoml+xml',
        'uri' => 'text/uri-list',
        'uris' => 'text/uri-list',
        'urls' => 'text/uri-list',
        'ustar' => 'application/x-ustar',
        'utz' => 'application/vnd.uiq.theme',
        'uu' => 'text/x-uuencode',
        'uva' => 'audio/vnd.dece.audio',
        'uvd' => 'application/vnd.dece.data',
        'uvf' => 'application/vnd.dece.data',
        'uvg' => 'image/vnd.dece.graphic',
        'uvh' => 'video/vnd.dece.hd',
        'uvi' => 'image/vnd.dece.graphic',
        'uvm' => 'video/vnd.dece.mobile',
        'uvp' => 'video/vnd.dece.pd',
        'uvs' => 'video/vnd.dece.sd',
        'uvt' => 'application/vnd.dece.ttml+xml',
        'uvu' => 'video/vnd.uvvu.mp4',
        'uvv' => 'video/vnd.dece.video',
        'uvva' => 'audio/vnd.dece.audio',
        'uvvd' => 'application/vnd.dece.data',
        'uvvf' => 'application/vnd.dece.data',
        'uvvg' => 'image/vnd.dece.graphic',
        'uvvh' => 'video/vnd.dece.hd',
        'uvvi' => 'image/vnd.dece.graphic',
        'uvvm' => 'video/vnd.dece.mobile',
        'uvvp' => 'video/vnd.dece.pd',
        'uvvs' => 'video/vnd.dece.sd',
        'uvvt' => 'application/vnd.dece.ttml+xml',
        'uvvu' => 'video/vnd.uvvu.mp4',
        'uvvv' => 'video/vnd.dece.video',
        'uvvx' => 'application/vnd.dece.unspecified',
        'uvvz' => 'application/vnd.dece.zip',
        'uvx' => 'application/vnd.dece.unspecified',
        'uvz' => 'application/vnd.dece.zip',
        'vcard' => 'text/vcard',
        'vcd' => 'application/x-cdlink',
        'vcf' => 'text/x-vcard',
        'vcg' => 'application/vnd.groove-vcard',
        'vcs' => 'text/x-vcalendar',
        'vcx' => 'application/vnd.vcx',
        'vis' => 'application/vnd.visionary',
        'viv' => 'video/vnd.vivo',
        'vob' => 'video/x-ms-vob',
        'vor' => 'application/vnd.stardivision.writer',
        'vox' => 'application/x-authorware-bin',
        'vrml' => 'model/vrml',
        'vsd' => 'application/vnd.visio',
        'vsf' => 'application/vnd.vsf',
        'vss' => 'application/vnd.visio',
        'vst' => 'application/vnd.visio',
        'vsw' => 'application/vnd.visio',
        'vtu' => 'model/vnd.vtu',
        'vxml' => 'application/voicexml+xml',
        'w3d' => 'application/x-director',
        'wad' => 'application/x-doom',
        'wav' => 'audio/x-wav',
        'wax' => 'audio/x-ms-wax',
        'wbmp' => 'image/vnd.wap.wbmp',
        'wbs' => 'application/vnd.criticaltools.wbs+xml',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wcm' => 'application/vnd.ms-works',
        'wdb' => 'application/vnd.ms-works',
        'wdp' => 'image/vnd.ms-photo',
        'weba' => 'audio/webm',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
        'wg' => 'application/vnd.pmi.widget',
        'wgt' => 'application/widget',
        'wks' => 'application/vnd.ms-works',
        'wm' => 'video/x-ms-wm',
        'wma' => 'audio/x-ms-wma',
        'wmd' => 'application/x-ms-wmd',
        'wmf' => 'application/x-msmetafile',
        'wml' => 'text/vnd.wap.wml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmls' => 'text/vnd.wap.wmlscript',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'wmv' => 'video/x-ms-wmv',
        'wmx' => 'video/x-ms-wmx',
        'wmz' => 'application/x-msmetafile',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'wpd' => 'application/vnd.wordperfect',
        'wpl' => 'application/vnd.ms-wpl',
        'wps' => 'application/vnd.ms-works',
        'wqd' => 'application/vnd.wqd',
        'wri' => 'application/x-mswrite',
        'wrl' => 'model/vrml',
        'wsdl' => 'application/wsdl+xml',
        'wspolicy' => 'application/wspolicy+xml',
        'wtb' => 'application/vnd.webturbo',
        'wvx' => 'video/x-ms-wvx',
        'x32' => 'application/x-authorware-bin',
        'x3d' => 'model/x3d+xml',
        'x3db' => 'model/x3d+binary',
        'x3dbz' => 'model/x3d+binary',
        'x3dv' => 'model/x3d+vrml',
        'x3dvz' => 'model/x3d+vrml',
        'x3dz' => 'model/x3d+xml',
        'xaml' => 'application/xaml+xml',
        'xap' => 'application/x-silverlight-app',
        'xar' => 'application/vnd.xara',
        'xbap' => 'application/x-ms-xbap',
        'xbd' => 'application/vnd.fujixerox.docuworks.binder',
        'xbm' => 'image/x-xbitmap',
        'xdf' => 'application/xcap-diff+xml',
        'xdm' => 'application/vnd.syncml.dm+xml',
        'xdp' => 'application/vnd.adobe.xdp+xml',
        'xdssc' => 'application/dssc+xml',
        'xdw' => 'application/vnd.fujixerox.docuworks',
        'xenc' => 'application/xenc+xml',
        'xer' => 'application/patch-ops-error+xml',
        'xfdf' => 'application/vnd.adobe.xfdf',
        'xfdl' => 'application/vnd.xfdl',
        'xht' => 'application/xhtml+xml',
        'xhtml' => 'application/xhtml+xml',
        'xhvml' => 'application/xv+xml',
        'xif' => 'image/vnd.xiff',
        'xla' => 'application/vnd.ms-excel',
        'xlam' => 'application/vnd.ms-excel.addin.macroenabled.12',
        'xlc' => 'application/vnd.ms-excel',
        'xlf' => 'application/x-xliff+xml',
        'xlm' => 'application/vnd.ms-excel',
        'xls' => 'application/vnd.ms-excel',
        'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
        'xlsm' => 'application/vnd.ms-excel.sheet.macroenabled.12',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlt' => 'application/vnd.ms-excel',
        'xltm' => 'application/vnd.ms-excel.template.macroenabled.12',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xlw' => 'application/vnd.ms-excel',
        'xm' => 'audio/xm',
        'xml' => 'application/xml',
        'xo' => 'application/vnd.olpc-sugar',
        'xop' => 'application/xop+xml',
        'xpi' => 'application/x-xpinstall',
        'xpl' => 'application/xproc+xml',
        'xpm' => 'image/x-xpixmap',
        'xpr' => 'application/vnd.is-xpr',
        'xps' => 'application/vnd.ms-xpsdocument',
        'xpw' => 'application/vnd.intercon.formnet',
        'xpx' => 'application/vnd.intercon.formnet',
        'xsl' => 'application/xml',
        'xslt' => 'application/xslt+xml',
        'xsm' => 'application/vnd.syncml+xml',
        'xspf' => 'application/xspf+xml',
        'xul' => 'application/vnd.mozilla.xul+xml',
        'xvm' => 'application/xv+xml',
        'xvml' => 'application/xv+xml',
        'xwd' => 'image/x-xwindowdump',
        'xyz' => 'chemical/x-xyz',
        'xz' => 'application/x-xz',
        'yang' => 'application/yang',
        'yin' => 'application/yin+xml',
        'z1' => 'application/x-zmachine',
        'z2' => 'application/x-zmachine',
        'z3' => 'application/x-zmachine',
        'z4' => 'application/x-zmachine',
        'z5' => 'application/x-zmachine',
        'z6' => 'application/x-zmachine',
        'z7' => 'application/x-zmachine',
        'z8' => 'application/x-zmachine',
        'zaz' => 'application/vnd.zzazz.deck+xml',
        'zip' => 'application/zip',
        'zir' => 'application/vnd.zul',
        'zirz' => 'application/vnd.zul',
        'zmm' => 'application/vnd.handheld-entertainment+xml',
        123 => 'application/vnd.lotus-1-2-3',
    ];

    /**
     * @var array 默认转义映射表
     */
    private static $_transliteration = [
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
        'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
        'ß' => 'ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
        'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
        'ÿ' => 'y',
    ];

    /**
     * @var array 要发送的头
     */
    private $_headers = [];

    /**
     * @var int HTTP状态码
     */
    private $_statusCode = 200;

    /**
     * @var bool 是否已经发送过
     */
    private $_isSent = false;

    /**
     * @var mixed 要发送的数据
     */
    private $_content;

    /**
     * @var resource 要发送的文件句柄
     */
    private $_stream;

    /**
     * 设置要发送的头
     * @param string|array $name header名,如果此参数是一个数组,则会判断为一个header数组,value无效
     * @param string $value header值
     * @return $this
     */
    public function setHeader($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->setHeader($k, $v);
            }
        } else {
            $this->_headers[strtolower($name)] = $value;
        }

        return $this;
    }

    /**
     * 发送已经设置的头信息,如果头已经发送了,那么这些头不会再发送
     * @return $this
     */
    private function sendHeaders()
    {
        if (!headers_sent($file,$line)) {
            foreach ($this->_headers as $name => $value) {
                $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
                header("$name: $value");
            }
            http_response_code($this->_statusCode);
        }
        return $this;
    }

    /**
     * 设置HTTP状态码
     * @param int $code
     * @return $this
     */
    public function setStatusCode($code)
    {
        $code = intval($code);
        if (isset(self::$_httpCode[$code])) {
            $this->_statusCode = $code;
        }
        return $this;
    }

    /**
     * 设置要发送的数据
     * @param mixed $data 要发送的数据
     * @return $this
     */
    public function setContent($data)
    {
        $this->_content = $data;
        return $this;
    }

    /**
     * 预处理输出内容
     * @throws \Exception 发送的内容格式不正确时抛出异常
     */
    private function prepare()
    {
        if ($this->_stream === null) {
            if (is_array($this->_content)) {
                throw new \Exception('Response content must not be an array.');
            } elseif (is_object($this->_content)) {
                if (method_exists($this->_content, '__toString')) {
                    $this->_content = $this->_content->__toString();
                } else {
                    throw new \Exception('Response content must be a string or an object implementing __toString().');
                }
            }
        }
    }

    /**
     * 发送内容
     */
    private function sendContent()
    {
        if ($this->_stream === null) {
            echo $this->_content;
        } else {
            set_time_limit(0);
            $chunkSize = 8 * 1024 * 1024;
            while (!feof($this->_stream)) {
                echo fread($this->_stream, $chunkSize);
                flush();
            }
            fclose($this->_stream);
        }
    }

    /**
     * 发送响应
     */
    public function send()
    {
        if ($this->_isSent == false) {
            $this->prepare();
            $this->sendHeaders();
            $this->sendContent();
            $this->_isSent = true;
        }
    }

    /**
     * 发送文件
     * @param string $filePath 文件的路径
     * @param string $attachmentName 要保存的文件名,如果没写则使用原来的的文件名
     * @param string $mime 文件的mime,不写的话根据文件后缀去判断,否则就是application/octet-stream
     * @param bool $inline 文件的输出类型是否是inline,否则是attachment
     * @return $this
     */
    public function sendFile($filePath, $attachmentName = null, $mime = null, $inline = false)
    {
        if (!$mime) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = isset(self::$_mimeTypes[$ext]) ? self::$_mimeTypes[$ext] : 'application/octet-stream';
        }

        if (!$attachmentName) {
            $attachmentName = basename($filePath);
        }

        $handle = fopen($filePath, 'rb');

        fseek($handle, 0, SEEK_END);
        $fileSize = ftell($handle);

        $this->setDownloadHeaders($attachmentName, $mime, $inline, $fileSize);

        $this->_stream = $handle;

        $this->setStatusCode(200);

        return $this;
    }

    /**
     * 发送字符串当做文件内容
     * @param string $content 文件内容
     * @param string $attachmentName 要保存的文件名
     * @param string $mime 文件的mime,不写的话就是application/octet-stream
     * @param bool $inline 文件的输出类型是否是inline,否则是attachment
     * @return $this
     */
    public function sendContentAsFile($content, $attachmentName, $mime = null, $inline = false)
    {
        $mime || ($mime = 'application/octet-stream');

        $fileSize = mb_strlen($content, '8bit');

        $this->setDownloadHeaders($attachmentName, $mime, $inline, $fileSize);

        $this->setStatusCode(200);

        return $this;
    }

    /**
     * 设置发送的文件头
     * @param string $attachmentName 要保存的文件名
     * @param string $mime 文件的MIME
     * @param bool $inline 是否inline
     * @param int $fileSize 文件字节数
     */
    private function setDownloadHeaders($attachmentName, $mime, $inline, $fileSize)
    {
        $disposition = $inline ? 'inline' : 'attachment';
        $this->setHeader('Pragma', 'public')
            ->setHeader('Accept-Ranges', 'bytes')
            ->setHeader('Expires', '0')
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Length', $fileSize)
            ->setHeader('Content-Disposition', $this->getDispositionHeaderValue($disposition, $attachmentName));
    }

    /**
     * 返回Content-Disposition的值
     * @param string $disposition inline或者attachment
     * @param string $attachmentName 文件名
     * @return string 返回转义后的Content-Disposition值
     */
    private function getDispositionHeaderValue($disposition, $attachmentName)
    {
        if (extension_loaded('intl')) {
            $fallbackName = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\uffff] remove', $attachmentName);
        } else {
            $fallbackName = strtr($attachmentName, static::$_transliteration);
        }
        $fallbackName = str_replace(['%', '/', '\\', '"'], ['_', '_', '_', '\\"'], $fallbackName);

        $utfName = rawurlencode(str_replace(['%', '/', '\\'], '', $attachmentName));

        $dispositionHeader = "{$disposition}; filename=\"{$fallbackName}\"";

        if ($utfName !== $fallbackName) {
            $dispositionHeader .= "; filename*=utf-8''{$utfName}";
        }

        return $dispositionHeader;
    }

    /**
     * 使用X-Sendfile下载文件
     * @param string $filePath 文件路径
     * @param string $attachmentName 要保存的文件名
     * @param string $mime 文件的mime,不写的话根据文件后缀去判断,否则就是application/octet-stream
     * @param bool $inline 文件的输出类型是否是inline,否则是attachment
     * @param string $xHeader 使用的HTTP头,默认为nginx的X-Accel-Redirect,apache用X-Sendfile
     * @return $this
     */
    public function xSendFile($filePath, $attachmentName = null, $mime = null, $inline = false, $xHeader = 'X-Accel-Redirect')
    {
        if (!$mime) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = isset(self::$_mimeTypes[$ext]) ? self::$_mimeTypes[$ext] : 'application/octet-stream';
        }

        if (!$attachmentName) {
            $attachmentName = basename($filePath);
        }

        $disposition = $inline ? 'inline' : 'attachment';

        $this->setHeader($xHeader, $filePath)
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', $this->getDispositionHeaderValue($disposition, $attachmentName));

        return $this;
    }

    /**
     * HTTP重定向
     * ```
     * redirect('get', ['id' => 100]);跳转到[当前模块/当前控制器/get]
     * redirect('admin/post', ['id' => 100]);跳转到[当前模块/admin/post]
     * redirect('lying/index/name', ['id' => 100]);跳转到[lying/index/name],参见URL生成
     * redirect('/admin/post', ['id' => 100]);跳转到[/admin/post/?id=100]
     * redirect('https://www.baidu.com') 必须带协议头,跳转到百度
     * ```
     * @param string $url 重定向的地址
     * @param array $params URL参数
     * @param bool $normal 是否把参数设置成?a=1&b=2,默认否,优先pathinfo,只对非/开头的path
     * @param int $statusCode HTTP状态码,默认302,如果ajax请求出错,可以手动设置200
     * @return $this
     */
    public function redirect($url, array $params = [], $normal = false, $statusCode = 302)
    {
        if (preg_match('/^https?:\/\/\S+/i', $url)) {
            $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
            $url = rtrim($url, '?&');
            if (!empty($query)) {
                $url .= (strpos($url, '?') === false ? "?$query" : "&$query");
            }
        } else {
            $url = \Lying::$maker->router->createUrl($url, $params, true, $normal);
        }

        if (\Lying::$maker->request->isPjax()) {
            $this->setHeader('X-Pjax-Url', $url);
        } elseif (\Lying::$maker->request->isAjax()) {
            $this->setHeader('X-Redirect', $url);
        } else {
            $this->setHeader('Location', $url);
        }

        $this->setStatusCode($statusCode);

        return $this;
    }
}
