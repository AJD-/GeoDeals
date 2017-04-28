import { User } from './user';
import { Injectable } from '@angular/core';
import { Http, Headers, Response, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/toPromise';

@Injectable()
export class UserRepository {
    private _apiUrl = 'http://54.70.252.84/api/profile';

	constructor(private http: Http) {}

	listAll() : Promise<User[]>{
		return this.http
			.get(this._apiUrl)
			.toPromise()
			.then(x => x.json().data as User[])
			.catch(x => x.message);
	}

	get(user: User) : Promise<User>{
		return this.http
			.get(`${this._apiUrl}/${user.username}`)
			.toPromise()
			.then(x => x.json().data as User)
			.catch(x => x.message);
	}
	
	add(user: User): Promise<User>{
        let headers = new Headers();
        headers.append('Content-Type', 'application/json');

        let options = new RequestOptions({ headers: headers });
		return this.http
			.post(this._apiUrl, JSON.stringify(user), options)
			.toPromise()
			.then(x => x.json().data as User)
			.catch(x => x.message);
			
	}
	
	update(user: User) : Promise<User>{
        return this.http
            .put(`${this._apiUrl}/${user.username}`, user)
			.toPromise()
			.then(() => user)
			.catch(x => x.message);
	}

	delete(user: User) : Promise<void>{
        return this.http
            .delete(`${this._apiUrl}/${user.username}`)
			.toPromise()
			.catch(x => x.message);
	}
}