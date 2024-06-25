<?php declare(strict_types=1);

const CLIENT_INTERFACE_SCHEMA = <<<'SCHEMA'
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "definitions": {
    "CancelAction": {
      "additionalProperties": false,
      "type": "object"
    },
    "PlaceEffortAction": {
      "additionalProperties": false,
      "type": "object"
    },
    "PlayerActions": {
      "additionalProperties": false,
      "type": "object"
    },
    "SelectionAction": {
      "additionalProperties": false,
      "type": "object"
    },
    "CardsMovedNotif": {
      "additionalProperties": false,
      "type": "object"
    },
    "EffortMovedNotif": {
      "additionalProperties": false,
      "type": "object"
    },
    "EffortPlacedNotif": {
      "additionalProperties": false,
      "type": "object"
    },
    "NotifTypes": {
      "additionalProperties": false,
      "type": "object"
    },
    "BoardState": {
      "additionalProperties": false,
      "properties": {
        "players": {
          "additionalProperties": {
            "$ref": "#/definitions/PublicPlayerState"
          },
          "type": "object"
        },
        "seats": {
          "additionalProperties": {
            "$ref": "#/definitions/PublicSeatState"
          },
          "type": "object"
        },
        "seatsPrivate": {
          "additionalProperties": {
            "$ref": "#/definitions/PrivateSeatState"
          },
          "type": "object"
        },
        "setlocs": {
          "additionalProperties": {
            "$ref": "#/definitions/SetlocState"
          },
          "type": "object"
        },
        "tableConfig": {
          "$ref": "#/definitions/TableConfig"
        }
      },
      "required": [
        "players",
        "seats",
        "seatsPrivate",
        "tableConfig",
        "setlocs"
      ],
      "type": "object"
    },
    "Card": {
      "additionalProperties": false,
      "properties": {
        "cardId": {
          "type": "number"
        },
        "cardType": {
          "type": "string"
        },
        "location": {
          "type": "string"
        },
        "locationArg": {
          "type": "number"
        },
        "state": {
          "$ref": "#/definitions/CardState"
        }
      },
      "required": [
        "cardId",
        "location",
        "locationArg",
        "state"
      ],
      "type": "object"
    },
    "CardState": {
      "enum": [
        "FACEUP",
        "FACEDOWN"
      ],
      "type": "string"
    },
    "GameStates": {
      "additionalProperties": false,
      "type": "object"
    },
    "Gamedatas": {
      "additionalProperties": false,
      "properties": {
        "boardState": {
          "$ref": "#/definitions/BoardState"
        }
      },
      "required": [
        "boardState"
      ],
      "type": "object"
    },
    "PrivateSeatState": {
      "additionalProperties": false,
      "properties": {
        "hand": {
          "items": {
            "$ref": "#/definitions/Card"
          },
          "type": "array"
        },
        "seatId": {
          "type": "number"
        }
      },
      "required": [
        "seatId",
        "hand"
      ],
      "type": "object"
    },
    "PublicPlayerState": {
      "additionalProperties": false,
      "type": "object"
    },
    "PublicSeatState": {
      "additionalProperties": false,
      "properties": {
        "colorName": {
          "type": "string"
        },
        "name": {
          "type": "string"
        },
        "playerId": {
          "type": "string"
        },
        "reserveEffort": {
          "type": "number"
        },
        "seatId": {
          "type": "number"
        }
      },
      "required": [
        "seatId",
        "name",
        "colorName",
        "reserveEffort"
      ],
      "type": "object"
    },
    "SetlocState": {
      "additionalProperties": false,
      "properties": {
        "cards": {
          "items": {
            "$ref": "#/definitions/Card"
          },
          "type": "array"
        },
        "effort": {
          "additionalProperties": {
            "type": "number"
          },
          "type": "object"
        },
        "locationId": {
          "type": "string"
        },
        "setlocId": {
          "type": "number"
        },
        "settingId": {
          "type": "string"
        }
      },
      "required": [
        "setlocId",
        "settingId",
        "locationId",
        "effort",
        "cards"
      ],
      "type": "object"
    },
    "TableConfig": {
      "additionalProperties": false,
      "type": "object"
    }
  }
}
SCHEMA
;
