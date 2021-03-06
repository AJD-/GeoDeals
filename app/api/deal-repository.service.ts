import { Injectable } from '@angular/core';
import { Http, Headers, Response, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/toPromise';

import { Deal } from './deal';

@Injectable()
export class DealRepository {
    private _apiUrl = 'http://54.70.252.84/api/deals/search';

	private _deals: Deal[];

    constructor(private http: Http){
    }

    public list(): Promise<Deal[]> {
        return this.http
            .get(this._apiUrl)
            .toPromise()
            .then(x => {
                let body = x.json();
                return (body.deals.final_deals) as Deal[];
            })
            .catch(x => x.message);
    }

    public get(id: number): Promise<Deal> {
        return this.http
            .get(`${this._apiUrl}/${id}`)
            .toPromise()
            .then(x => x.json().data as Deal)
            .catch(x => x.message);
    }

    public add(deal: Deal): Promise<Deal> {
        return this.http
            .post(this._apiUrl, deal)
            .toPromise()
            .then(x => x.json().data as Deal)
            .catch(x => x.message);
    }

    public update(deal: Deal): Promise<Deal> {
        return this.http
            .put(`${this._apiUrl}/${deal.id}`, deal)
            .toPromise()
            .then(() => deal)
            .catch(x => x.message);
    }

    public delete(deal: Deal): Promise<void> {
        return this.http
            .delete(`${this._apiUrl}/${deal.id}`)
            .toPromise()
            .catch(x => x.message);
    }
}