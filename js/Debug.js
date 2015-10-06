/**
 *	Strut Creative
 *  @project Novatel
 *  @docket nov-1
 *	
 *	Debug convienance library. Call these debug statements insetad of console.log
 *	Should be able to detect when complex data is being provided 
 *	and either parse it out for the correct output
 *	or simply use the correct browser debug statements 
 *	(console.log vs console.dir vs console.notice vs console.error etc/etc)
 *	
 **/

var Strut = Strut || {};

//I dont anticipate any namespace extensions for this project, so we won't bother with extending
Strut.Debug = {
	//I'm thinking right now, we can have multiple levels of severity
	//	Highest		CRITICAL
	//	|			ERROR
	//	|			WARNING
	//	|			DEBUG
	//	Lowest		NOTICE

	// I'm also thinking we setup "sections" if desired in the calls, to disable entire sections of logs
	Level: 5,
	EnabledSections: [],
	NOTICE: 5,
	DEBUG: 4,
	WARNING: 3,
	ERROR: 2,
	CRITICAL: 1,
	// default is DEBUG

	SetLevel: function(lvl){
		if(lvl > 5) lvl = 5;
		if(lvl < 0) lvl = 0;
		Strut.Debug.Level = lvl;
	},

	EnableSection: function(section){
		if(Strut.Debug.EnabledSections.indexOf(section) === -1)
			Strut.Debug.EnabledSections.push(section);
	},

	DisableSection: function(section){
		var idx = Strut.Debug.EnabledSections.indexOf(section);
		if(idx !== -1)
			Strut.Debug.EnabledSections.splice(idx,1);
	},

	Log: function(msg, section, type){
		if(typeof section !== 'undefined' && Strut.Debug.EnabledSections.indexOf(section) === -1)
			return; //bail here if we have provided a section that is not enabled
		if(Strut.Debug.Level < type)
			return; //bail here since we don't have this level enabled
		switch(type){
			case Strut.Debug.CRITICAL:
				console.error(msg);
				break;
			case Strut.Debug.ERROR:
				console.error(msg);
				break;
			case Strut.Debug.WARNING:
				console.warn(msg);
				break;
			case Strut.Debug.NOTICE:
				console.info(msg);
				break;
			//case Strut.Debug.DEBUG:
			default:
				console.log(msg);
				break;
		}
	},

	//Trace: function(section, type){
		//this will generate a stack trace, and log it
	//},

	Notice: function(msg, section){
		Strut.Debug.Log(msg,section,Strut.Debug.NOTICE);
	},

	Debug: function(msg, section){
		Strut.Debug.Log(msg,section,Strut.Debug.DEBUG);
	},

	Warning: function(msg, section){
		Strut.Debug.Log(msg,section,Strut.Debug.WARNING);
	},

	Error: function(msg, section){
		Strut.Debug.Log(msg,section,Strut.Debug.ERROR);
	},

	Critical: function(msg, section){
		Strut.Debug.Log(msg,section,Strut.Debug.CRITICAL);
	},

};

var Debug = Strut.Debug;