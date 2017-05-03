import { Component, Input, Output, EventEmitter } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { User } from '../api/user';
import { UserRepository } from '../api/user-repository.service';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  selector: 'signup',
  templateUrl: './app/signup/signup.component.html',
  styleUrls: [ './app/signup/signup.component.css' ]
})

export class SignupComponent { 
	@Output() titleUpdated : EventEmitter<string> = new EventEmitter();
    title: string;
    user: any = {};

    constructor(private router: Router,
                private route: ActivatedRoute,
                private userRepository: UserRepository) {
		this.title = "Sign Up";
        this.titleUpdated.emit(this.title); 
    }
    submit() {
        if(!this.user.email_marketing)
            this.user.email_marketing = 0;
        this.userRepository.add(this.user)
            .then(x => {
                this.goToLogin(`User registered`);
            });
    }

    goToLogin(message) {
        this.router.navigateByUrl('login')
            .then(() => console.log(message));
    }
}
