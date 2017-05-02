import { Injectable } from '@angular/core';
import { InMemoryDbService } from 'angular-in-memory-web-api';

@Injectable()
export class MockApiService implements InMemoryDbService {

    dataStore = {
        default: {
            deals: [
                { id: 1, title: 'Deal 1', description: "SAMPLE DESCRIPTION", imagePath: 'img/tilted.png', rating: 20 },
                { id: 2, title: 'Deal 2', description: "SAMPLE DESCRIPTION", imagePath: 'img/unknown.png', rating: 35 },
                { id: 3, title: 'Deal 3', description: "SAMPLE DESCRIPTION", imagePath: 'img/wat.png', rating: 10 }
            ]
        },
        empty: {
            deals: []
        }
    };

    createDb() {
        return this.dataStore['default'];
    }
}