import { Component, Input, Output, EventEmitter } from '@angular/core';


@Component({
  selector: 'login',
  templateUrl: './app/login/login.component.html',
  styleUrls: [ './app/login/login.component.css' ]
})

export class LoginComponent { 
	@Output() titleUpdated : EventEmitter<string> = new EventEmitter();
	title : string;

	constructor(){
		this.title = "Login";
		this.titleUpdated.emit(this.title);
	}
}
