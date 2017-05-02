import { Component, Input, Output, EventEmitter } from '@angular/core';
import { UserRepository } from '../api/user-repository.service';
import { Router, ActivatedRoute } from '@angular/router';


@Component({
  selector: 'login',
  templateUrl: './app/login/login.component.html',
  styleUrls: [ './app/login/login.component.css' ]
})

export class LoginComponent { 
	@Output() titleUpdated : EventEmitter<string> = new EventEmitter();
    title: string;
    user: any = {};
    error: any;

    constructor(private router: Router,
        private route: ActivatedRoute,
        private userRepository: UserRepository){
		this.title = "Login";
		this.titleUpdated.emit(this.title);
    }

    submit() {
        this.userRepository.signin(this.user)
            .then(x => {
                if (localStorage.getItem('jwt'))
                    this.goToFeed(`User logged in`);
                else
                    this.error = localStorage.getItem('error');
            })
            .catch(x => {
                this.loginError("Invalid Username or password");
            }
        );
    }

    goToFeed(message) {
        this.router.navigateByUrl('feed')
            .then(() => console.log(message));
    }

    loginError(message) {
        this.user.password = "";
        alert(message);
    }
}
