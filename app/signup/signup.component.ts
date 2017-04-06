import { Component, Input, Output, EventEmitter } from '@angular/core';


@Component({
  selector: 'signup',
  templateUrl: './app/signup/signup.component.html',
  styleUrls: [ './app/signup/signup.component.css' ]
})

export class SignupComponent { 
	@Output() titleUpdated : EventEmitter<string> = new EventEmitter();
	title : string;

	constructor(){
		this.title = "Sign Up";
		this.titleUpdated.emit(this.title);
	}
}
