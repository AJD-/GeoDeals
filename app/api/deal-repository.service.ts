import { Injectable } from '@angular/core';
import { Http, Headers, Response, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/toPromise';

import { Deal } from './deal';

@Injectable()
export class DealRepository {
    private _apiUrl = 'https://54.70.252.84/api/deals/search';
    private _apiPostDealUrl = 'https://54.70.252.84/api/deal';

    constructor(private http: Http){
    }

    public list(): Promise<Deal[]> {
        var headers = new Headers();
        headers.append('Authorization', localStorage.getItem('Authorization'));
        let options = new RequestOptions({ headers: headers });
        return this.http
            .get(this._apiUrl, options)
            .toPromise()
            .then(x => {
                let body = x.json();
                return (body.deals.final_deals) as Deal[];
            })
            .catch(x => x.message);
    }
    public search(searchParam): Promise<Deal[]> {
        var headers = new Headers();
        headers.append('Authorization', localStorage.getItem('Authorization'));
        let options = new RequestOptions({ headers: headers });
        return this.http
            .post(this._apiUrl, searchParam, options)
            .toPromise()
            .then(x => {
                let body = x.json();
                return (body.deals.final_deals) as Deal[];
            })
            .catch(x => x.message);
    }
    public get(id: number): Promise<Deal> {
        var headers = new Headers();
        headers.append("Authorization", localStorage.getItem('Authorization'));
        let options = new RequestOptions({ headers: headers });
        return this.http
            .get(`${this._apiPostDealUrl}/${id}`, options)
            .toPromise()
            .then(x => x.json().data as Deal)
            .catch(x => x.message);
    }

    public add(formData): Promise<Deal> {
        let headers = new Headers();
        headers.append('Content-Type', 'multipart/form-data');
        let options = new RequestOptions({ headers: headers });
        return this.http
            .post(this._apiPostDealUrl, formData, options)
            .toPromise()
            .then(x => x.json().data as Deal)
            .catch(x => x.message);
    }

    public update(deal: Deal): Promise<Deal> {
        return this.http
            .put(`${this._apiPostDealUrl}/${deal.deal_id}`, deal)
            .toPromise()
            .then(() => deal)
            .catch(x => x.message);
    }

    public delete(deal: Deal): Promise<void> {
        return this.http
            .delete(`${this._apiPostDealUrl}/${deal.deal_id}`)
            .toPromise()
            .catch(x => x.message);
    }
}