import { Component, Input, Output, EventEmitter } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { User } from '../api/user';


@Component({
  selector: 'signup',
  templateUrl: './app/signup/signup.component.html',
  styleUrls: [ './app/signup/signup.component.css' ]
})

export class SignupComponent { 
	@Output() titleUpdated : EventEmitter<string> = new EventEmitter();
    title: string;
    user: any = {};

	constructor(){
		this.title = "Sign Up";
        this.titleUpdated.emit(this.title);
    }
    submit() {

    }
}
