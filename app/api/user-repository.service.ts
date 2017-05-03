import { User } from './user';
import { Injectable } from '@angular/core';
import { Http, Headers, Response, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/toPromise';

@Injectable()
export class UserRepository {
    private _apiUrl = 'https://54.70.252.84/api/profile';
    private _signInUrl = 'https://54.70.252.84/api/signin';

    private Authorization: string;

	constructor(private http: Http) {}

    listAll(): Promise<User[]>{
        var headers = new Headers();
        headers.append("Authorization", this.Authorization);
        let options = new RequestOptions({ headers: headers });
		return this.http
			.get(this._apiUrl, options)
			.toPromise()
			.then(x => x.json().data as User[])
			.catch(x => x.message);
	}

    get(user: User): Promise<User>{
        var headers = new Headers();
        headers.append("Authorization", this.Authorization);
        let options = new RequestOptions({ headers: headers });
		return this.http
			.get(`${this._apiUrl}/${user.username}`, options)
			.toPromise()
			.then(x => x.json().data as User)
			.catch(x => x.message);
	}
	
    add(user: User): Promise<User>{
		return this.http
			.post(this._apiUrl, user)
			.toPromise()
            .then(x => {
                if (x.json().token) {
                    this.Authorization = x.json().token;
                    localStorage.setItem('Authorization', this.Authorization);
                } else {
                    localStorage.setItem('error', (x.json().error.text));
                }
            })
			.catch(x => x.message);
	}
	
    update(user: User): Promise<User>{
        var headers = new Headers();
        headers.append("Authorization", this.Authorization);
        let options = new RequestOptions({ headers: headers });
        return this.http
            .put(`${this._apiUrl}/${user.username}`, user, options)
			.toPromise()
			.then(() => user)
			.catch(x => x.message);
	}

    delete(user: User): Promise<void>{
        var headers = new Headers();
        headers.append("Authorization", this.Authorization);
        let options = new RequestOptions({ headers: headers });
        return this.http
            .delete(`${this._apiUrl}/${user.username}`, options)
			.toPromise()
			.catch(x => x.message);
    }

    signin(user: any): Promise<User> {
        return this.http
            .post(this._signInUrl, user)
            .toPromise()
            .then(x => {
                if (x.json().token) {
                    this.Authorization = x.json().token;
                    localStorage.setItem('Authorization', this.Authorization);
                } else {
                    localStorage.setItem('error', (x.json().error.text));
                }
            })
            .catch(x => x.message);
    }
}