# RandomColorEveryDay
Website and API that will generate new random color every day.
[http://randomcoloreveryday.com/](http://randomcoloreveryday.com/)

*Please note that the domain isn't currently running. Please use [http://http://random-color-of-the-day.funsite.cz/](http://random-color-of-the-day.funsite.cz/) instead.*

## Use on web
### Classes
Just import <a href="http://randomcoloreveryday.com/stylesheet" target="_blank">`http://randomcoloreveryday.com/stylesheet`</a> as a stylesheet and use classes you want. You will get new color for your website every day. 
* **s1** to **s10** = Shades (dark)
* **t1** to **t10** = Tints (light)
* **to1** to **to9** = Tones (changing saturation)
* **ana1** to **ana7** = Analogous colors
* **com1** to **com7** = Complementary colors
* **sim1** to **sim7** = Similar colors
* **tri1** to **tri6** = Triadic colors
* **randomColorEveryDay** = today's color
* **foreground-text** and **foreground-text-gray** = black/white (or dark/light gray) based on lightness of today's color (should be redable)

Classes set *color* to these values. If you put a suffix **-back** to the class, it will set *backgroud*.
```html
<link rel="stylesheet" href="http://randomcoloreveryday.com/stylesheet">
...
<div class="randomColorEveryDay-back foreground-text">This div has today's color as a background and black or white text (based on today's color).</div>
<div class="t7 com4-back">This div has a complementary color as a background and a light text (tint 7).</div>
```

### CSS variables
Other option is to import <a href="http://randomcoloreveryday.com/stylesheet-variables" target="_blank">`http://randomcoloreveryday.com/stylesheet-variables`</a> as a stylesheet and use [css variables](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_variables) in your own second stylesheet file (this is not compatible with all browsers - currently [not supported in IE and Edge](http://caniuse.com/#search=CSS%20Variables)).

Variable names are same as class names (plus some extra variables for R,G,B and H,S,V - look into the file).
```html
//page.html
<link rel="stylesheet" href="http://randomcoloreveryday.com/stylesheet-variables">
<link rel="stylesheet" href="mystyle.css">
```
```css
//mystyle.css
#id-selector {
	color: var(--foreground-text);
	background: var(--ana7);
}
```

### Generate schemes from a color
Or if you just want to generate the schemes, add /HEXCODE with the hex code of the color you want (without #).
```html
<link rel="stylesheet" href="http://randomcoloreveryday.com/stylesheet/FFE000">
```

## API
You can use <a href="http://randomcoloreveryday.com/color" target="_blank">`http://randomcoloreveryday.com/color`</a> as an API with specified parameters of the color you want to know (names are same as the css variables + listed below). Color parameter is not necessary and will use today's color if not specified.
### Function parameters:
`params`: color parameters you want to get, separated by delimiter (',')
* **randomColorEveryDay** or **color** = today's color
* RGB
	* **Red** or **R** 
	* **Green** or **G** 
	* **Blue** or **B** 
* HSV 
	* **Hue** or **H** 
	* **Saturation** or **S** 
	* **Value** or **V** 
* HSL 
	* **Hue2** or **H2** 
	* **Saturation2** or **S2** 
	* **Lightness** or **L** 
	
`delimiter`: delimiter of params (your input and also output), default is ','

`color`: optional parameter, the color used for generating scheme (hex code without #); if not set, today's color is used

```
http://randomcoloreveryday.com/color?params=R,G,B&color=FFE000 -> 255,224,0
```
