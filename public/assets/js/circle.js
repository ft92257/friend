(function ($) {
    
var requestAnimationFrame = window.requestAnimationFrame    ||
    window.webkitRequestAnimationFrame  ||
    window.mozRequestAnimationFrame     ||
    window.oRequestAnimationFrame       ||
    window.msRequestAnimationFrame || function(callback){ return window.setTimeout(callback, 1000/60); };


    function Plugin(t,o){
        this.target=t;
        this.options=o;
        t.addClass('circle');
        this.init(this.options);
    }

    Plugin.prototype = {
        init : function(o){
            var that = this, $this = that.target;
            that.canvas = $this.find('canvas').get(0);
            that.context = that.canvas.getContext('2d');
            this.x = this.canvas.width / 2;
            this.y = this.canvas.height / 2;
            this.degrees = o.percent/100 * 360.0;
            this.radians = this.degrees * (Math.PI / 180);   //弧度
            this.radius = that.canvas.width / 2.5;
            this.startAngle = 0;
            this.endAngle =3/2 * Math.PI;
            this.curPerc = 0.0;
            this.endPercent = o.percent;
            this.curStep = Math.max(o.animationstep, 0.0);
            this.additionStep = (o.lineCap == "round") ? (o.bordersize)/this.radius : 0;   //控制linecap为round的长度溢出状况
             this.startPI = (o.startdegree / 180) * Math.PI - Math.PI/2;
            that.animate();
        },

        animate : function(current){
            
            var that = this,o = that.options,context = that.context,canvas = that.canvas;
            context.clearRect(0, 0, canvas.width, canvas.height);
            
            context.beginPath();
            context.arc(this.x, this.y, this.radius, 0,Math.PI*2, false);  //  bg Circle
            context.lineWidth = o.bordersize;
            context.strokeStyle = o.bgcolor; 
            context.stroke();
            if(!Modernizr.canvas){
                context.beginPath();
                context.arc(this.x, this.y, this.radius, this.startPI, (Math.PI*2*o.percent/100) + this.startPI, false);
                context.strokeStyle = o.fgcolor;
                context.stroke();
               return;
            }
            if(typeof(current)!=='undefined'){
                context.beginPath();
                context.arc(this.x, this.y, this.radius, this.startPI, (Math.PI*2*current) + this.startPI -this.additionStep , false);
                context.strokeStyle = o.fgcolor;
                //context.lineCap="round";
                context.stroke();
            } 
           
            if (this.curPerc < this.endPercent) {
                this.curPerc += this.curStep;
                requestAnimationFrame(function () {
                    that.animate(Math.min(that.curPerc, that.endPercent)/100);
                }, that);
            }else if (this.curPerc >= this.endPercent) {
                context.arc(this.x, this.y, this.radius, this.startPI, this.startPI+Math.PI*2 , false);
                if (o.complete) {
                    o.complete.call(that);
                }
                return false;
            }
        }

    };
   
    $.fn.circle = function(o) {
        var settings = $.extend({
            startdegree: 0,
            fgcolor: "#FF503F",
            bgcolor: "#e7e7e7",
            fill: false,
            width: 15,
            lineCap : 'but',
            fontsize: 15,
            percent: 50,
            animationstep: 1.0,
            border: 'default',
            complete: null,
            bordersize: 10
        }, o);
        
        return this.each(function(index) {
            var me = $(this);  
                return new Plugin(me,settings);
        });
    };  

}(jQuery));
