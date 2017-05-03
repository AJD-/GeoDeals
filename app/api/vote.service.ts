import { Vote } from './vote';
import { Injectable } from '@angular/core';
import { Http, Headers, Response, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/toPromise';

@Injectable()
export class VoteService {
    private _apiUrl = 'https://54.70.252.84/api/vote';

    constructor(private http: Http) { }

    public vote(vote: Vote) {
        var headers = new Headers();
        headers.append('Authorization', localStorage.getItem('Authorization'));
        let options = new RequestOptions({ headers: headers });
        return this.http
            .post(this._apiUrl, vote, options)
            .toPromise()
            .then(x => x.json().data as Vote)
            .catch(x => x.message);
    }
}