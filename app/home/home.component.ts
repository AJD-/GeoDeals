import { Component, Input, Output, EventEmitter } from '@angular/core';


@Component({
  selector: 'home',
  templateUrl: './app/home/home.component.html',
  styleUrls: [ './app/home/home.component.css' ]
})

export class HomeComponent { 
	@Output() titleUpdated : EventEmitter<string> = new EventEmitter();
	title : string;

	constructor(){
		this.title = "Login/SignUp";
		this.titleUpdated.emit(this.title);
	}
}
