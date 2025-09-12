<?php 
$head = file_get_contents('https://raw.githubusercontent.com/sustance/configs/refs/heads/main/php/head-land.html');
echo $head;
?>

<?php

//Store the svg icon URL in a variable
$svg_icon_url = 'https://github.com/sustance/sustance.github.io/blob/main/assets/home.svg';

//Use file_get_contents() to retrieve the content of the svg
$svg_icon_content = file_get_contents( $svg_icon_url );

//Modify the SVG content based on your needs

//Sanitize the SVG content. In this example I will use https://packagist.org/packages/enshrined/svg-sanitize package
require 'vendor/autoload.php';
use enshrined\svgSanitize\Sanitizer;
$sanitizer = new Sanitizer();
$svg_icon_content = $sanitizer->sanitize($svg_icon_content);

//Print the SVG HTML
echo $svg_icon_content;
?>




<div class="bsd">
<p>
<i>DE.</i> <b>b</b>sd.tilde.team <span class="sml">157.90.196.52</span> 
<a href="https://bsd.tilde.team/">b.t.t</a> 
<a href="https://bsd.tilde.team/~identity2">/~i52</a> 
<a href="http://b.identity2.com">b.i.c</a>
<br>

<i>US.</i> <b>c</b>trl-c.club <span class="tin">165.227.127.54</span> 
<a href="https://ctrl-c.club/">c.c</a>
<a href="https://ctrl-c.club/~identity2">/~i52</a>
<a href="https://ctrl-c.club/~identity2/lineage/">/lin</a> 
<a href="http://c.identity2.com">c.i.c~</a>
<br>

<i>GB.</i> <b>d</b>imesion.sh <span class="sml">209.97.187.90</span> 
<a href="https://dimension.sh/">d.s</a> 
<a href="https://dimension.sh/~identity2">/~i42</a> 
<a href="http://d.identity2.com">d.i.c</a>
<br> 

<i>DE.</i> <b>e</b>nvs.net <span class="sml">89.163.145.170</span> 
<a href="https://envs.net/">e.n</a>  
<a href="https://envs.net/~identity2">/~i52</a>  
  <a href="https://e.identity2.com">e.i.c</a>
<i>remi</i> 
<br>
  
<i>DE.</i> <b>f</b>reeshell.de <span class="sml">116.202.128.144</span> 
<a href="https://freeshell.de/">f.d</a> 
<a href="https://freeshell.de/~identity">/~i4y</a> 
    <a href="http://f.identity2.com">f.i.c</a>
<br>

<i>CA.</i>  tilde.<b>g</b>uru <span class="sml">95.179.178.246</span> 
<a href="https://tilde.guru">t.g</a>  
<a href="https://tilde.guru/~edi">/~edi</a>
<a href="http://g.identity2.com">g.i.c</a>
<br>

<i>CA.</i>  t<b>h</b>unix.net <span class="sml">142.44.150.185</span> 
<a href="https://thunix.net">t.n</a>  
<a href="https://thunix.net/~id">/~id</a> 
<a href="https://tilde.tel">t.tel</a>  
<a href="http://h.identity2.com">h.i.c</a>
<br>

<i>DE.</i> tilde.<b>i</b>nstitute. <span class="sml">opt-svr</span>
<span class="sml">159.69.146.152</span>  
<a href="https://tilde.institute">t.i</a>  
<a href="https://id2.tilde.institute">id2.t.i</a> 
<a href="http://i.identity2.com">i.i.c</a>
<br>

<i>DE.</i> pro<b>j</b>ectsegfau.lt <span class="sml">45.145.41.226</span> 
<a href="https://projectsegfau.lt/">p.l</a> 
<a href="https://identity2.p.projectsegfau.lt/">i.p.p.l/~i52</a> 
<a href="http://j.identity2.com">j.i.c</a>
<br>

<i>DE.</i> pr<b>o</b>jectsegfau.lt <span class="sml">45.145.41.226</span>
<a href="https://projectsegfau.lt/">p.l</a> 
<a href="https://id.p.projectsegfau.lt/">i.p.p.l/~id</a>
<a href="http://o.identity2.com">o.i.c</a>                                              
<br>

<i>CA.</i> tilde.<b>p</b>ink  
<span class="sml">198.50.210.248 </span> <a href="https://tilde.pink/">t.p</a>  
<a href="https://tilde.pink/~id2">t.p/~id2</a> 
  <a href="https://p.identity2.com">p.i.c</a>
<br>

<i>CA.</i> tilde.<b>t</b>eam <span class="sml">198.50.210.248</span>  <a href="https://tilde.team/">t.t</a> 
<a href="https://tilde.team/~identity2">/~i52</a> 
<a href="https://tilde.team/~identity2/lineage">/lin</a> 
<a href="http://t.identity2.com">t.i.c</a>
<br>

  
<i>US</i> gith<b>u</b>b.io <span class="sml">45.33.66.185</span> 
<a href="https://sustance.github.io">s.g.io</a>
<a href="https://identity2.com">i.c</a>
<a href="http://identity2.com">www.i.c</a>
<br>
  

<i>US.</i> <b>v</b>ern.cc <span class="sml">5.161.108.85</span> 
<a href="https://vern.cc/">v.c</a> 
no web
<br>

<i>US</i> rawte<b>x</b>t.club <span class="sml">45.33.66.185</span> 
<a href="https://rawtext.club/">r.c</a> 
no web
<br>

  
<i>HK.</i> <span class="sml">Huawei64</span> Hu<b>4</b>
<a href="https://sufbo.tplinkdns.com:8004">sufbo.tplinkdns.com:8004</a>
<a href="http://4.identity2.com">4.i.c</a>
<br>

  
<i>HK.</i> <span class="sml">HPMini32</span> Mi<b>7</b>
<a href="https://sufbo.tplinkdns.com:8007">sufbo.tplinkdns.com:8007</a>
<a href="http://7.identity2.com">7.i.c</a>
</p> 
</div>


<div class="box"> 
<p><i>Mob</i> <b>le</b><span class="sml">novo</p>
<p>SIP Address	2233589729@sip2sip.info<br>
Username	2233589729  PASS: ;;6sip<br>
Domain	sip2sip.info<br>
Other SIP servers available To locate SIP device must lookup DNS<br>
<br>
Acct settings, login using credentials http://x.sip2sip.info<br>
FAIL sufbo, k.m@g.c<br>
<br>
Zoiper Test Server<br>
Domain: zoiper.com<br>
Preconfigured test account for Zoiper softphone<br>
<br>
Quick setup for basic SIP testing<br>
Use username: test, password: test1234<br>
Server: sip.zoiper.com (port 5060)</p>
</div>
<!--
<svg xmlns="http://www.w3.org/2000/svg">
    <pattern id="pattern" width="8" height="8" patternUnits="userSpaceOnUse">
      <path d="m0 0h1v1H0"/>
    </pattern>
    <filter id="filter">
        <feMorphology operator="dilate" radius="3 0" result="h"/>
        <feOffset in="SourceAlpha" dx="4" dy="4" result="o"/>
        <feMorphology operator="dilate" radius="0 3"/>
        <feBlend in="h" result="g"/>
        <feBlend in="SourceAlpha" in2="o" result="l"/>
        <feTurbulence baseFrequency=".3"/>
        <feComposite operator="in" in="l"/>
        <feMorphology operator="dilate" radius="1"/>
        <feComposite operator="in" in="l"/>
        <feComponentTransfer>
            <feFuncA type="discrete" tableValues="0 1 1 1 1"/>
        </feComponentTransfer>
        <feMorphology operator="dilate" radius="3"/>
        <feComposite operator="in" in="g"/>
        <feOffset dx="4"/>
        <feBlend in="SourceAlpha"/>
    </filter>
    <rect width="100%" height="800" fill="#e6ffff"/>
    <rect width="100%" height="300" fill="url(#pattern)" filter="url(#filter)" transform="scale(3)"/>
</svg>
 rgba(93,173,226,.2)
-->
  
<div class="svg-container">
    <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="75" id="preview-svg">
    <pattern id="pattern" width="8" height="8" patternUnits="userSpaceOnUse">
    <path d="m0 0h1v1H0"       fill="#dddddd"      />
    </pattern>
                        <filter id="filter">
                            <feMorphology operator="dilate" radius="3 0" result="h"/>
                            <feOffset in="SourceAlpha" dx="4" dy="4" result="o"/>
                            <feMorphology operator="dilate" radius="0 3"/>
                            <feBlend in="h" result="g"/>
                            <feBlend in="SourceAlpha" in2="o" result="l"/>
                            <feTurbulence baseFrequency=".3"/>
                            <feComposite operator="in" in="l"/>
                            <feMorphology operator="dilate" radius="1"/>
                            <feComposite operator="in" in="l"/>
                            <feComponentTransfer>
                                <feFuncA type="discrete" tableValues="0 1 1 1 1"/>
                            </feComponentTransfer>
                            <feMorphology operator="dilate" radius="3"/>
                            <feComposite operator="in" in="g"/>
                            <feOffset dx="4"/>
                            <feBlend in="SourceAlpha"/>
                        </filter>
    <rect width="100%" height="50" fill="#e6ffff"/>
    <rect width="100%" height="75" fill="url(#pattern)" 
                              filter="url(#filter)" 
                              transform="scale(2)"/>
    </svg>
                    <!--
                    The first rect element creates the solid background.
                    The second rect applies the pattern and filter effects. scale is useful
                    -->
</div>
  
<?php
$tail = file_get_contents('https://raw.githubusercontent.com/sustance/sustance.github.io/refs/heads/main/tail-land.html');
echo $tail;
?>
