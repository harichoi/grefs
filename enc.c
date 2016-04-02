#include <stdio.h>

#include <string.h>



void makepass(char *str,char *shakey){

int i;

str[0]=str[0]+shakey[0];

for(i=1 ; i<512 ; i++)

str[i]=str[i]+str[i-1]+shakey[i%strlen(shakey)];

}


void passsolve(char *block,char *shakey){

block[0]=(block[0])-(shakey[0]);
char before[512];

memecpy(before,shakey,512);

for(i=1 ; i<512 ; i++){
block[i]=block[i]-before[i-1]-shakey[i%strlen(shakey)];
}
}



int main(int argc,char *args[]){

char *block=new char[512];

char *ss=new char[512];

char *file=new char[512];

char md5[]="839d7228b5ccc60e54455093a966b8c2";

char sha1[]="2147a9e5f9e89b70810c82ff8e12817130d6ca10";
sprintf(file,"%s",args[1]);

FILE *in=fopen(file,"rb");

sprintf(ss,"%s.enc",file);

FILE *out=fopen(ss,"wb");


while(!feof(in)){

fread(block,512,1,in);
if(feof(in))break;

passsolve(block,sha1);

fwrite(block,512,1,out);

}



fclose(in);

fclose(out);

return 0;

}