function JPEGEncoder(quality){var self=this;var fround=Math.round;var ffloor=Math.floor;var YTable=new Array(64);var UVTable=new Array(64);var fdtbl_Y=new Array(64);var fdtbl_UV=new Array(64);var YDC_HT;var UVDC_HT;var YAC_HT;var UVAC_HT;var bitcode=new Array(65535);var category=new Array(65535);var outputfDCTQuant=new Array(64);var DU=new Array(64);var byteout=[];var bytenew=0;var bytepos=7;var YDU=new Array(64);var UDU=new Array(64);var VDU=new Array(64);var clt=new Array(256);var RGB_YUV_TABLE=new Array(2048);var currentQuality;var ZigZag=[0,1,5,6,14,15,27,28,2,4,7,13,16,26,29,42,3,8,12,17,25,30,41,43,9,11,18,24,31,40,44,53,10,19,23,32,39,45,52,54,20,22,33,38,46,51,55,60,21,34,37,47,50,56,59,61,35,36,48,49,57,58,62,63];var std_dc_luminance_nrcodes=[0,0,1,5,1,1,1,1,1,1,0,0,0,0,0,0,0];var std_dc_luminance_values=[0,1,2,3,4,5,6,7,8,9,10,11];var std_ac_luminance_nrcodes=[0,0,2,1,3,3,2,4,3,5,5,4,4,0,0,1,0x7d];var std_ac_luminance_values=[0x01,0x02,0x03,0x00,0x04,0x11,0x05,0x12,0x21,0x31,0x41,0x06,0x13,0x51,0x61,0x07,0x22,0x71,0x14,0x32,0x81,0x91,0xa1,0x08,0x23,0x42,0xb1,0xc1,0x15,0x52,0xd1,0xf0,0x24,0x33,0x62,0x72,0x82,0x09,0x0a,0x16,0x17,0x18,0x19,0x1a,0x25,0x26,0x27,0x28,0x29,0x2a,0x34,0x35,0x36,0x37,0x38,0x39,0x3a,0x43,0x44,0x45,0x46,0x47,0x48,0x49,0x4a,0x53,0x54,0x55,0x56,0x57,0x58,0x59,0x5a,0x63,0x64,0x65,0x66,0x67,0x68,0x69,0x6a,0x73,0x74,0x75,0x76,0x77,0x78,0x79,0x7a,0x83,0x84,0x85,0x86,0x87,0x88,0x89,0x8a,0x92,0x93,0x94,0x95,0x96,0x97,0x98,0x99,0x9a,0xa2,0xa3,0xa4,0xa5,0xa6,0xa7,0xa8,0xa9,0xaa,0xb2,0xb3,0xb4,0xb5,0xb6,0xb7,0xb8,0xb9,0xba,0xc2,0xc3,0xc4,0xc5,0xc6,0xc7,0xc8,0xc9,0xca,0xd2,0xd3,0xd4,0xd5,0xd6,0xd7,0xd8,0xd9,0xda,0xe1,0xe2,0xe3,0xe4,0xe5,0xe6,0xe7,0xe8,0xe9,0xea,0xf1,0xf2,0xf3,0xf4,0xf5,0xf6,0xf7,0xf8,0xf9,0xfa];var std_dc_chrominance_nrcodes=[0,0,3,1,1,1,1,1,1,1,1,1,0,0,0,0,0];var std_dc_chrominance_values=[0,1,2,3,4,5,6,7,8,9,10,11];var std_ac_chrominance_nrcodes=[0,0,2,1,2,4,4,3,4,7,5,4,4,0,1,2,0x77];var std_ac_chrominance_values=[0x00,0x01,0x02,0x03,0x11,0x04,0x05,0x21,0x31,0x06,0x12,0x41,0x51,0x07,0x61,0x71,0x13,0x22,0x32,0x81,0x08,0x14,0x42,0x91,0xa1,0xb1,0xc1,0x09,0x23,0x33,0x52,0xf0,0x15,0x62,0x72,0xd1,0x0a,0x16,0x24,0x34,0xe1,0x25,0xf1,0x17,0x18,0x19,0x1a,0x26,0x27,0x28,0x29,0x2a,0x35,0x36,0x37,0x38,0x39,0x3a,0x43,0x44,0x45,0x46,0x47,0x48,0x49,0x4a,0x53,0x54,0x55,0x56,0x57,0x58,0x59,0x5a,0x63,0x64,0x65,0x66,0x67,0x68,0x69,0x6a,0x73,0x74,0x75,0x76,0x77,0x78,0x79,0x7a,0x82,0x83,0x84,0x85,0x86,0x87,0x88,0x89,0x8a,0x92,0x93,0x94,0x95,0x96,0x97,0x98,0x99,0x9a,0xa2,0xa3,0xa4,0xa5,0xa6,0xa7,0xa8,0xa9,0xaa,0xb2,0xb3,0xb4,0xb5,0xb6,0xb7,0xb8,0xb9,0xba,0xc2,0xc3,0xc4,0xc5,0xc6,0xc7,0xc8,0xc9,0xca,0xd2,0xd3,0xd4,0xd5,0xd6,0xd7,0xd8,0xd9,0xda,0xe2,0xe3,0xe4,0xe5,0xe6,0xe7,0xe8,0xe9,0xea,0xf2,0xf3,0xf4,0xf5,0xf6,0xf7,0xf8,0xf9,0xfa];function initQuantTables(sf){var YQT=[16,11,10,16,24,40,51,61,12,12,14,19,26,58,60,55,14,13,16,24,40,57,69,56,14,17,22,29,51,87,80,62,18,22,37,56,68,109,103,77,24,35,55,64,81,104,113,92,49,64,78,87,103,121,120,101,72,92,95,98,112,100,103,99];for(var i=0;i<64;i++){var t=ffloor((YQT[i]*sf+50)/100);if(t<1){t=1;}else if(t>255){t=255;}
YTable[ZigZag[i]]=t;}
var UVQT=[17,18,24,47,99,99,99,99,18,21,26,66,99,99,99,99,24,26,56,99,99,99,99,99,47,66,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99,99];for(var j=0;j<64;j++){var u=ffloor((UVQT[j]*sf+50)/100);if(u<1){u=1;}else if(u>255){u=255;}
UVTable[ZigZag[j]]=u;}
var aasf=[1.0,1.387039845,1.306562965,1.175875602,1.0,0.785694958,0.541196100,0.275899379];var k=0;for(var row=0;row<8;row++)
{for(var col=0;col<8;col++)
{fdtbl_Y[k]=(1.0/(YTable[ZigZag[k]]*aasf[row]*aasf[col]*8.0));fdtbl_UV[k]=(1.0/(UVTable[ZigZag[k]]*aasf[row]*aasf[col]*8.0));k++;}}}
function computeHuffmanTbl(nrcodes,std_table){var codevalue=0;var pos_in_table=0;var HT=new Array();for(var k=1;k<=16;k++){for(var j=1;j<=nrcodes[k];j++){HT[std_table[pos_in_table]]=[];HT[std_table[pos_in_table]][0]=codevalue;HT[std_table[pos_in_table]][1]=k;pos_in_table++;codevalue++;}
codevalue*=2;}
return HT;}
function initHuffmanTbl()
{YDC_HT=computeHuffmanTbl(std_dc_luminance_nrcodes,std_dc_luminance_values);UVDC_HT=computeHuffmanTbl(std_dc_chrominance_nrcodes,std_dc_chrominance_values);YAC_HT=computeHuffmanTbl(std_ac_luminance_nrcodes,std_ac_luminance_values);UVAC_HT=computeHuffmanTbl(std_ac_chrominance_nrcodes,std_ac_chrominance_values);}
function initCategoryNumber()
{var nrlower=1;var nrupper=2;for(var cat=1;cat<=15;cat++){for(var nr=nrlower;nr<nrupper;nr++){category[32767+nr]=cat;bitcode[32767+nr]=[];bitcode[32767+nr][1]=cat;bitcode[32767+nr][0]=nr;}
for(var nrneg=-(nrupper-1);nrneg<=-nrlower;nrneg++){category[32767+nrneg]=cat;bitcode[32767+nrneg]=[];bitcode[32767+nrneg][1]=cat;bitcode[32767+nrneg][0]=nrupper-1+nrneg;}
nrlower<<=1;nrupper<<=1;}}
function initRGBYUVTable(){for(var i=0;i<256;i++){RGB_YUV_TABLE[i]=19595*i;RGB_YUV_TABLE[(i+256)>>0]=38470*i;RGB_YUV_TABLE[(i+512)>>0]=7471*i+0x8000;RGB_YUV_TABLE[(i+768)>>0]=-11059*i;RGB_YUV_TABLE[(i+1024)>>0]=-21709*i;RGB_YUV_TABLE[(i+1280)>>0]=32768*i+0x807FFF;RGB_YUV_TABLE[(i+1536)>>0]=-27439*i;RGB_YUV_TABLE[(i+1792)>>0]=-5329*i;}}
function writeBits(bs)
{var value=bs[0];var posval=bs[1]-1;while(posval>=0){if(value&(1<<posval)){bytenew|=(1<<bytepos);}
posval--;bytepos--;if(bytepos<0){if(bytenew==0xFF){writeByte(0xFF);writeByte(0);}
else{writeByte(bytenew);}
bytepos=7;bytenew=0;}}}
function writeByte(value)
{byteout.push(clt[value]);}
function writeWord(value)
{writeByte((value>>8)&0xFF);writeByte((value)&0xFF);}
function fDCTQuant(data,fdtbl)
{var d0,d1,d2,d3,d4,d5,d6,d7;var dataOff=0;var i;const I8=8;const I64=64;for(i=0;i<I8;++i)
{d0=data[dataOff];d1=data[dataOff+1];d2=data[dataOff+2];d3=data[dataOff+3];d4=data[dataOff+4];d5=data[dataOff+5];d6=data[dataOff+6];d7=data[dataOff+7];var tmp0=d0+d7;var tmp7=d0-d7;var tmp1=d1+d6;var tmp6=d1-d6;var tmp2=d2+d5;var tmp5=d2-d5;var tmp3=d3+d4;var tmp4=d3-d4;var tmp10=tmp0+tmp3;var tmp13=tmp0-tmp3;var tmp11=tmp1+tmp2;var tmp12=tmp1-tmp2;data[dataOff]=tmp10+tmp11;data[dataOff+4]=tmp10-tmp11;var z1=(tmp12+tmp13)*0.707106781;data[dataOff+2]=tmp13+z1;data[dataOff+6]=tmp13-z1;tmp10=tmp4+tmp5;tmp11=tmp5+tmp6;tmp12=tmp6+tmp7;var z5=(tmp10-tmp12)*0.382683433;var z2=0.541196100*tmp10+z5;var z4=1.306562965*tmp12+z5;var z3=tmp11*0.707106781;var z11=tmp7+z3;var z13=tmp7-z3;data[dataOff+5]=z13+z2;data[dataOff+3]=z13-z2;data[dataOff+1]=z11+z4;data[dataOff+7]=z11-z4;dataOff+=8;}
dataOff=0;for(i=0;i<I8;++i)
{d0=data[dataOff];d1=data[dataOff+8];d2=data[dataOff+16];d3=data[dataOff+24];d4=data[dataOff+32];d5=data[dataOff+40];d6=data[dataOff+48];d7=data[dataOff+56];var tmp0p2=d0+d7;var tmp7p2=d0-d7;var tmp1p2=d1+d6;var tmp6p2=d1-d6;var tmp2p2=d2+d5;var tmp5p2=d2-d5;var tmp3p2=d3+d4;var tmp4p2=d3-d4;var tmp10p2=tmp0p2+tmp3p2;var tmp13p2=tmp0p2-tmp3p2;var tmp11p2=tmp1p2+tmp2p2;var tmp12p2=tmp1p2-tmp2p2;data[dataOff]=tmp10p2+tmp11p2;data[dataOff+32]=tmp10p2-tmp11p2;var z1p2=(tmp12p2+tmp13p2)*0.707106781;data[dataOff+16]=tmp13p2+z1p2;data[dataOff+48]=tmp13p2-z1p2;tmp10p2=tmp4p2+tmp5p2;tmp11p2=tmp5p2+tmp6p2;tmp12p2=tmp6p2+tmp7p2;var z5p2=(tmp10p2-tmp12p2)*0.382683433;var z2p2=0.541196100*tmp10p2+z5p2;var z4p2=1.306562965*tmp12p2+z5p2;var z3p2=tmp11p2*0.707106781;var z11p2=tmp7p2+z3p2;var z13p2=tmp7p2-z3p2;data[dataOff+40]=z13p2+z2p2;data[dataOff+24]=z13p2-z2p2;data[dataOff+8]=z11p2+z4p2;data[dataOff+56]=z11p2-z4p2;dataOff++;}
var fDCTQuant;for(i=0;i<I64;++i)
{fDCTQuant=data[i]*fdtbl[i];outputfDCTQuant[i]=(fDCTQuant>0.0)?((fDCTQuant+0.5)|0):((fDCTQuant-0.5)|0);}
return outputfDCTQuant;}
function writeAPP0()
{writeWord(0xFFE0);writeWord(16);writeByte(0x4A);writeByte(0x46);writeByte(0x49);writeByte(0x46);writeByte(0);writeByte(1);writeByte(1);writeByte(0);writeWord(1);writeWord(1);writeByte(0);writeByte(0);}
function writeSOF0(width,height)
{writeWord(0xFFC0);writeWord(17);writeByte(8);writeWord(height);writeWord(width);writeByte(3);writeByte(1);writeByte(0x11);writeByte(0);writeByte(2);writeByte(0x11);writeByte(1);writeByte(3);writeByte(0x11);writeByte(1);}
function writeDQT()
{writeWord(0xFFDB);writeWord(132);writeByte(0);for(var i=0;i<64;i++){writeByte(YTable[i]);}
writeByte(1);for(var j=0;j<64;j++){writeByte(UVTable[j]);}}
function writeDHT()
{writeWord(0xFFC4);writeWord(0x01A2);writeByte(0);for(var i=0;i<16;i++){writeByte(std_dc_luminance_nrcodes[i+1]);}
for(var j=0;j<=11;j++){writeByte(std_dc_luminance_values[j]);}
writeByte(0x10);for(var k=0;k<16;k++){writeByte(std_ac_luminance_nrcodes[k+1]);}
for(var l=0;l<=161;l++){writeByte(std_ac_luminance_values[l]);}
writeByte(1);for(var m=0;m<16;m++){writeByte(std_dc_chrominance_nrcodes[m+1]);}
for(var n=0;n<=11;n++){writeByte(std_dc_chrominance_values[n]);}
writeByte(0x11);for(var o=0;o<16;o++){writeByte(std_ac_chrominance_nrcodes[o+1]);}
for(var p=0;p<=161;p++){writeByte(std_ac_chrominance_values[p]);}}
function writeSOS()
{writeWord(0xFFDA);writeWord(12);writeByte(3);writeByte(1);writeByte(0);writeByte(2);writeByte(0x11);writeByte(3);writeByte(0x11);writeByte(0);writeByte(0x3f);writeByte(0);}
function processDU(CDU,fdtbl,DC,HTDC,HTAC){var EOB=HTAC[0x00];var M16zeroes=HTAC[0xF0];var pos;const I16=16;const I63=63;const I64=64;var DU_DCT=fDCTQuant(CDU,fdtbl);for(var j=0;j<I64;++j){DU[ZigZag[j]]=DU_DCT[j];}
var Diff=DU[0]-DC;DC=DU[0];if(Diff==0){writeBits(HTDC[0]);}else{pos=32767+Diff;writeBits(HTDC[category[pos]]);writeBits(bitcode[pos]);}
var end0pos=63;for(;(end0pos>0)&&(DU[end0pos]==0);end0pos--){};if(end0pos==0){writeBits(EOB);return DC;}
var i=1;var lng;while(i<=end0pos){var startpos=i;for(;(DU[i]==0)&&(i<=end0pos);++i){}
var nrzeroes=i-startpos;if(nrzeroes>=I16){lng=nrzeroes>>4;for(var nrmarker=1;nrmarker<=lng;++nrmarker)
writeBits(M16zeroes);nrzeroes=nrzeroes&0xF;}
pos=32767+DU[i];writeBits(HTAC[(nrzeroes<<4)+category[pos]]);writeBits(bitcode[pos]);i++;}
if(end0pos!=I63){writeBits(EOB);}
return DC;}
function initCharLookupTable(){var sfcc=String.fromCharCode;for(var i=0;i<256;i++){clt[i]=sfcc(i);}}
this.encode=function(image,quality)
{var time_start=new Date().getTime();if(quality)setQuality(quality);byteout=new Array();bytenew=0;bytepos=7;writeWord(0xFFD8);writeAPP0();writeDQT();writeSOF0(image.width,image.height);writeDHT();writeSOS();var DCY=0;var DCU=0;var DCV=0;bytenew=0;bytepos=7;this.encode.displayName="_encode_";var imageData=image.data;var width=image.width;var height=image.height;var quadWidth=width*4;var tripleWidth=width*3;var x,y=0;var r,g,b;var start,p,col,row,pos;while(y<height){x=0;while(x<quadWidth){start=quadWidth*y+x;p=start;col=-1;row=0;for(pos=0;pos<64;pos++){row=pos>>3;col=(pos&7)*4;p=start+(row*quadWidth)+col;if(y+row>=height){p-=(quadWidth*(y+1+row-height));}
if(x+col>=quadWidth){p-=((x+col)-quadWidth+4)}
r=imageData[p++];g=imageData[p++];b=imageData[p++];YDU[pos]=((RGB_YUV_TABLE[r]+RGB_YUV_TABLE[(g+256)>>0]+RGB_YUV_TABLE[(b+512)>>0])>>16)-128;UDU[pos]=((RGB_YUV_TABLE[(r+768)>>0]+RGB_YUV_TABLE[(g+1024)>>0]+RGB_YUV_TABLE[(b+1280)>>0])>>16)-128;VDU[pos]=((RGB_YUV_TABLE[(r+1280)>>0]+RGB_YUV_TABLE[(g+1536)>>0]+RGB_YUV_TABLE[(b+1792)>>0])>>16)-128;}
DCY=processDU(YDU,fdtbl_Y,DCY,YDC_HT,YAC_HT);DCU=processDU(UDU,fdtbl_UV,DCU,UVDC_HT,UVAC_HT);DCV=processDU(VDU,fdtbl_UV,DCV,UVDC_HT,UVAC_HT);x+=32;}
y+=8;}
if(bytepos>=0){var fillbits=[];fillbits[1]=bytepos+1;fillbits[0]=(1<<(bytepos+1))-1;writeBits(fillbits);}
writeWord(0xFFD9);var jpegDataUri='data:image/jpeg;base64,'+btoa(byteout.join(''));byteout=[];var duration=new Date().getTime()-time_start;console.log('Encoding time: '+duration+'ms');return jpegDataUri}
function setQuality(quality){if(quality<=0){quality=1;}
if(quality>100){quality=100;}
if(currentQuality==quality)return
var sf=0;if(quality<50){sf=Math.floor(5000/quality);}else{sf=Math.floor(200-quality*2);}
initQuantTables(sf);currentQuality=quality;console.log('Quality set to: '+quality+'%');}
function init(){var time_start=new Date().getTime();if(!quality)quality=50;initCharLookupTable()
initHuffmanTbl();initCategoryNumber();initRGBYUVTable();setQuality(quality);var duration=new Date().getTime()-time_start;console.log('Initialization '+duration+'ms');}
init();};function getImageDataFromImage(idOrElement){var theImg=(typeof(idOrElement)=='string')?document.getElementById(idOrElement):idOrElement;var cvs=document.createElement('canvas');cvs.width=theImg.width;cvs.height=theImg.height;var ctx=cvs.getContext("2d");ctx.drawImage(theImg,0,0);return(ctx.getImageData(0,0,cvs.width,cvs.height));}