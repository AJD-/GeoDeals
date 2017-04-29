import { Vote } from './vote';
import { Injectable } from '@angular/core';
import { Http, Headers, Response, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/toPromise';

@Injectable()
export class VoteService {
    private _apiUrl = 'api/vote';

    constructor(private http: Http) { }

    public vote(vote: Vote) {
        return this.http
            .post(this._apiUrl, vote)
            .toPromise()
            .then(x => x.json().data as Vote)
            .catch(x => x.message);
    }
}