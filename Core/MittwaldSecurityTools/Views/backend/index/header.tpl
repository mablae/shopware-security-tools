{extends file="parent:backend/index/header.tpl"}

{* add our custom icon for the backend menu entry *}
{block name="backend/base/header/css" append}
    <style>
        .mittwald-custom-icon{
            width:0;
            height:0;
            padding: 8px;
            background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wcVDAYnABGUbgAAAipJREFUOMulks1LVFEYxn/3nDtnRsdyHMhAyxpjFCoKKojAFkG1ir5IcJMQtAiKFtW+lhXUHxBtpEADiz4kaGMEIhYmlIGJWC00NTJi7jgz995z72lxJ2ciIqMfnMX78vLwnPd9rNtDzUZrF08v0drYw6FrHokaxV8xoOoEdqBdikWXZFxz/2UG5CQl7bEilhTCABaGtqYj9A9/Ack/IQDcME+u1IXjzIEBvCB6QViZ9MJKvwobLFI1ioGxDNROgBdwo3s3MWExPPWVvpFp0CFXOneRrosRtyUX+0bJezoSsDC0rTvKpTtzYFu0pVdx4WA7rhewrSVF3+B7NjbVc/nYFlw3IB6XnOsZKXsHUdQOzlIXjjMPfsDJfVm8ss3t6xtAG84f3orrRr2CG+D7OvoqINI1cQbGWqE2AQWf7o4MWCCFRao2BvWKs3uz2MICYDFfivbxc4nta0/QOzQDtsWm5gZa0kkm5xwGJxcIteHumQ5UTHBzcIp4TPLxWwHCKoHv3nGcXNn+/iyeG9D7+hPGgB+EdO5sQYchj159wBjD58UC2JVbi4cjayCZgKLPqT0ZVEwy8HwaaVvLibv+dILNjQ3owDDvFMEWlTP2j82ChNWpJM/ezeP5IeMLOR6MzjA+k0PZkqtP3nBgxwZuvZjm8dtZUBUHVuL0PVPSfjks5ZAoGYUoML/XUoCM3CVsFQVpmSrlaJA/19VR/h9slRSQV78YWREGVFLwAyJr5UCSaEtvAAAAAElFTkSuQmCC');
            background-repeat: no-repeat;
        }
    </style>

{/block}