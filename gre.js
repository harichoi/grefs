function encoval(ch){
ch=floor(ch);
if(ch<10) return chr(ch+ord("0"));
return chr(ch-10+ord("a"));
}
function decoval(ch){
if(ord(ch)>=ord("a") && ord(ch)<=ord("z")) return ord(ch)-ord("a")+10;
return ord(ch)-ord("0");
}
function enco(str){
str=escape(str);
var i;
result="";
for(i=0 ; i<strlen(str) ; i++){
var uu=ord(str.charAt(i));
if(uu>=256){
var aa=floor(uu/256);
var bb=floor(uu%256);
result+=encoval(bb/16)+encoval(bb%16);
result+=encoval(aa/16)+encoval(aa%16);
alert(uu);
}
else
result+=encoval(uu/16)+encoval(uu%16);
}
return result;
}
function deco(str){
var i;
result="";
for(i=0 ; i<strlen(str) ; i+=2){
result+=chr(decoval(str.charCodeAt(i))*16+decoval(str.charCodeAt(i+1)));
}
return result;
}