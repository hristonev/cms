/**
 * Create animation to element
 * @param obj {DomNode} object for animation
 * @param type {string} type of animation. Call same named method
 * @param direction {string} x or y
 * @param type {int} start value
 * @param type {int} end value
 * @param type {int} time in miliseconds = speed of animation / 10
 * @param remove {bool} remove element after animation complete
 */
function animate(obj, type, direction, start, end, speed, remove){
	if(typeof(window.__animate) == "undefined"){
		window.__animate = new Array();
		window.__animate['instance'] = new Array();
	}
	this.instanceKey = window.__animate['instance'].length;
	window.__animate['instance'][this.instanceKey] = this;

	this.element = obj;
	this.direction = direction;
	this.start = start;
	this.end = end;
	this.speed = speed;
	this.remove = remove;
	this.stepAngle = Math.PI / 20;
	this.angle = 0;
	this.time = setInterval("window.__animate['instance'][" + this.instanceKey + "]." + type + "()", this.speed);

	this.expand = function(){
		this.angle += this.stepAngle;
		if(this.start < this.end){
			var value = Math.sin(this.angle) * this.end;
		}else{
			var value = Math.cos(this.angle) * this.start;
		}
		if(this.angle >= Math.PI / 2){
			var value = this.end;
			clearInterval(this.time);
			if(this.remove){
				this.element.destruct();
			}
		}
		if(this.direction == "y"){
			this.element.elm.style.height = value + "px";
		}else if(this.direction == "x"){
			this.element.elm.style.width = value + "px";
		}
	};
};