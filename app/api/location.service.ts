import { Injectable } from '@angular/core';
import { Http, Headers, Response, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/toPromise';

@Injectable()
export class LocationService {
    private _apiUrl = 'https://54.70.252.84/api/stores/search';

    constructor(private http: Http) { }

    public sendLoc(loc: any): Promise<any[]> {
        return this.http.post(this._apiUrl, loc)
            .toPromise()
            .then(x => {
                let body = x.json();
                return (body.deals) as any[];
            }).catch(x => x.message);
    }
}