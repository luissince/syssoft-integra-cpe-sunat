<?php

namespace App\Helpers;

use App\Src\NumberLleters;
use DateTime;
use DOMDocument;

class XmlGenerator
{

    public static function generateInvoiceXml($venta, $detalle, $empresa, $sucursal): DOMDocument
    {
        $sub_total = array_reduce($detalle, function ($acumulador, $producto) {
            $igv = floatval($producto->porcentaje) / 100.00;
            $sub_total = floatval($producto->cantidad) * (floatval($producto->precio) / ($igv  + 1));
            return $acumulador + $sub_total;
        });

        $impuesto = array_reduce($detalle, function ($acumulador, $producto) {
            $igv = floatval($producto->porcentaje) / 100.00;
            $sub_total = floatval($producto->precio) / ($igv  + 1);
            $impuesto = floatval($producto->cantidad) * ($sub_total * $igv);
            return $acumulador + $impuesto;
        });

        $total = array_reduce($detalle, function ($acumulador, $producto) {
            return $acumulador + floatval($producto->cantidad) * floatval($producto->precio);
        });

        $xml = new DOMDocument('1.0', 'utf-8');
        // $xml->standalone         = true;
        $xml->preserveWhiteSpace = false;

        $Invoice = $xml->createElement('Invoice');
        $Invoice = $xml->appendChild($Invoice);

        $Invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $Invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $Invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $Invoice->setAttribute('xmlns:ccts', "urn:un:unece:uncefact:documentation:2");
        $Invoice->setAttribute('xmlns:ds', "http://www.w3.org/2000/09/xmldsig#");
        $Invoice->setAttribute('xmlns:ext', "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
        $Invoice->setAttribute('xmlns:qdt', "urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
        $Invoice->setAttribute('xmlns:sac', "urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
        $Invoice->setAttribute('xmlns:udt', "urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
        $Invoice->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");

        $UBLExtension = $xml->createElement('ext:UBLExtensions');
        $UBLExtension = $Invoice->appendChild($UBLExtension);

        $ext = $xml->createElement('ext:UBLExtension');
        $ext = $UBLExtension->appendChild($ext);
        $contents = $xml->createElement('ext:ExtensionContent', ' ');
        $ext->appendChild($contents);

        $date = new DateTime($venta->fecha . "T" . $venta->hora);

        //Version de UBL 2.1
        $cbc = $xml->createElement('cbc:UBLVersionID', '2.1');
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:CustomizationID', '2.0');
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:ID', $venta->serie . '-' . $venta->numeracion);  // numero de factura
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:IssueDate', $date->format('Y-m-d'));   // fecha de emision
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:IssueTime', $date->format('H:i:s'));   // hora de emision
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:InvoiceTypeCode', $venta->codigoVenta);
        $cbc = $Invoice->appendChild($cbc);
        $cbc->setAttribute('listID', "0101");
        $cbc = $xml->createElement('cbc:Note');
        $cbc->appendChild($xml->createCDATASection(NumberLleters::getResult(round($total, 2, PHP_ROUND_HALF_UP), $venta->moneda)));
        $cbc = $Invoice->appendChild($cbc);
        $cbc->setAttribute('languageLocaleID', "1000");
        $cbc = $xml->createElement('cbc:DocumentCurrencyCode', $venta->codiso);
        $cbc = $Invoice->appendChild($cbc);

        // DATOS DE FIRMA
        $cac_signature = $xml->createElement('cac:Signature');
        $cac_signature = $Invoice->appendChild($cac_signature);
        $cbc = $xml->createElement('cbc:ID',  $empresa->documento);
        $cbc = $cac_signature->appendChild($cbc);
        $cac_signatory = $xml->createElement('cac:SignatoryParty');
        $cac_signatory = $cac_signature->appendChild($cac_signatory);
        $cac = $xml->createElement('cac:PartyIdentification');
        $cac = $cac_signatory->appendChild($cac);
        $cbc = $xml->createElement('cbc:ID',  $empresa->documento);
        $cbc = $cac->appendChild($cbc);
        $cac = $xml->createElement('cac:PartyName');
        $cac = $cac_signatory->appendChild($cac);
        $cbc = $xml->createElement('cbc:Name');
        $cbc->appendChild($xml->createCDATASection($empresa->razonSocial));
        $cbc = $cac->appendChild($cbc);
        $cac = $xml->createElement('cac:ExternalReference');
        $cac_digital = $xml->createElement('cac:DigitalSignatureAttachment');
        $cac_digital = $cac_signature->appendChild($cac_digital);
        $cac = $cac_digital->appendChild($cac);
        $cbc = $xml->createElement('cbc:URI', '#SysSoftIntegra');
        $cbc = $cac->appendChild($cbc);


        // DATOS EMISOR
        $cac_SupplierParty = $xml->createElement('cac:AccountingSupplierParty');
        $cac_SupplierParty = $Invoice->appendChild($cac_SupplierParty);
        $cac_party = $xml->createElement('cac:Party');
        $cac_party = $cac_SupplierParty->appendChild($cac_party);
        $PartyIdentification = $xml->createElement('cac:PartyIdentification');
        $PartyIdentification = $cac_party->appendChild($PartyIdentification);
        $cbc = $xml->createElement('cbc:ID', $empresa->documento);
        $cbc->setAttribute('schemeID', $empresa->codigo);
        $cbc = $PartyIdentification->appendChild($cbc);
        $PartyName = $xml->createElement('cac:PartyName');
        $PartyName = $cac_party->appendChild($PartyName);
        $cbc = $xml->createElement('cbc:Name');
        $cbc->appendChild($xml->createCDATASection($empresa->nombreEmpresa));
        $cbc = $PartyName->appendChild($cbc);
        $PartyLegalEntity = $xml->createElement('cac:PartyLegalEntity');
        $PartyLegalEntity = $cac_party->appendChild($PartyLegalEntity);
        $cbc = $xml->createElement('cbc:RegistrationName');
        $cbc->appendChild($xml->createCDATASection($empresa->razonSocial));
        $cbc = $PartyLegalEntity->appendChild($cbc);
        $RegistrationAddress = $xml->createElement('cac:RegistrationAddress');
        $RegistrationAddress = $PartyLegalEntity->appendChild($RegistrationAddress);
        $cbc = $xml->createElement('cbc:ID', $sucursal->ubigeo);
        $cbc = $RegistrationAddress->appendChild($cbc);
        $cbc = $xml->createElement('cbc:AddressTypeCode', "0000");
        $cbc = $RegistrationAddress->appendChild($cbc);
        $cbc = $xml->createElement('cbc:CityName', $sucursal->provincia);
        $cbc = $RegistrationAddress->appendChild($cbc);
        $cbc = $xml->createElement('cbc:CountrySubentity', $sucursal->departamento);
        $cbc = $RegistrationAddress->appendChild($cbc);
        $cbc = $xml->createElement('cbc:District', $sucursal->distrito);
        $cbc = $RegistrationAddress->appendChild($cbc);
        $AddressLine = $xml->createElement('cac:AddressLine');
        $AddressLine = $RegistrationAddress->appendChild($AddressLine);
        $cbc = $xml->createElement('cbc:Line');
        $cbc->appendChild($xml->createCDATASection($sucursal->direccion));
        $cbc = $AddressLine->appendChild($cbc);
        $CountryLine = $xml->createElement('cac:Country');
        $CountryLine = $RegistrationAddress->appendChild($CountryLine);
        $cbc = $xml->createElement('cbc:IdentificationCode', 'PE');
        $cbc = $CountryLine->appendChild($cbc);

        // DATOS DEL CLIENTE
        $cac_CustomerParty = $xml->createElement('cac:AccountingCustomerParty');
        $cac_CustomerParty = $Invoice->appendChild($cac_CustomerParty);
        $cac_party = $xml->createElement('cac:Party');
        $cac_party = $cac_CustomerParty->appendChild($cac_party);
        $PartyIdentification = $xml->createElement('cac:PartyIdentification');
        $PartyIdentification = $cac_party->appendChild($PartyIdentification);
        $cbc = $xml->createElement('cbc:ID', $venta->documento);
        $cbc->setAttribute('schemeID', $venta->codigoCliente);
        $cbc = $PartyIdentification->appendChild($cbc);
        $PartyLegalEntity = $xml->createElement('cac:PartyLegalEntity');
        $PartyLegalEntity = $cac_party->appendChild($PartyLegalEntity);
        $cbc = $xml->createElement('cbc:RegistrationName');
        $cbc->appendChild($xml->createCDATASection($venta->informacion));
        $cbc = $PartyLegalEntity->appendChild($cbc);

        //FORMA PAGO

        if ($venta->formaVenta == 'CONTADO' || $venta->formaVenta == 'ADELANTADO') {
            $PaymentTerms = $xml->createElement('cac:PaymentTerms');
            $PaymentTerms = $Invoice->appendChild($PaymentTerms);
            $cbc = $xml->createElement('cbc:ID', "FormaPago");
            $cbc = $PaymentTerms->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PaymentMeansID', "Contado");
            $cbc = $PaymentTerms->appendChild($cbc);
        }

        if ($venta->formaVenta == 'CRÉDITO FIJO') {
            $PaymentTerms = $xml->createElement('cac:PaymentTerms');
            $PaymentTerms = $Invoice->appendChild($PaymentTerms);
            $cbc = $xml->createElement('cbc:ID', "FormaPago");
            $cbc = $PaymentTerms->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PaymentMeansID', "Credito");
            $cbc = $PaymentTerms->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Amount', number_format(round($total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $PaymentTerms->appendChild($cbc);

            $PaymentTerms = $xml->createElement('cac:PaymentTerms');
            $PaymentTerms = $Invoice->appendChild($PaymentTerms);
            $cbc = $xml->createElement('cbc:ID', "FormaPago");
            $cbc = $PaymentTerms->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PaymentMeansID', "Cuota001");
            $cbc = $PaymentTerms->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Amount', number_format(round($total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $PaymentTerms->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PaymentDueDate', date("Y-m-d", strtotime($venta->FechaVencimiento)));
            $cbc = $PaymentTerms->appendChild($cbc);
        }

        if ($venta->formaVenta == 'CRÉDITO VARIABLE') {
            // $PaymentTerms = $xml->createElement('cac:PaymentTerms');
            // $PaymentTerms = $Invoice->appendChild($PaymentTerms);
            // $cbc = $xml->createElement('cbc:ID', "FormaPago");
            // $cbc = $PaymentTerms->appendChild($cbc);
            // $cbc = $xml->createElement('cbc:PaymentMeansID', "Credito");
            // $cbc = $PaymentTerms->appendChild($cbc);
            // $cbc = $xml->createElement('cbc:Amount', number_format(round($detalleventa[0]['totalconimpuesto'], 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            // $cbc->setAttribute('currencyID', $venta->codiso);
            // $cbc = $PaymentTerms->appendChild($cbc);

            // $countPm = 0;
            // foreach ($credito as $value) {
            //     $countPm++;
            //     $cuotaV = $countPm <= 9 ? "Cuota00" . $countPm : "Cuota" . $countPm;
            //     $PaymentTerms = $xml->createElement('cac:PaymentTerms');
            //     $PaymentTerms = $Invoice->appendChild($PaymentTerms);
            //     $cbc = $xml->createElement('cbc:ID', "FormaPago");
            //     $cbc = $PaymentTerms->appendChild($cbc);
            //     $cbc = $xml->createElement('cbc:PaymentMeansID', $cuotaV);
            //     $cbc = $PaymentTerms->appendChild($cbc);
            //     $cbc = $xml->createElement('cbc:Amount', number_format(round($value->Monto, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            //     $cbc->setAttribute('currencyID', $venta->codiso);
            //     $cbc = $PaymentTerms->appendChild($cbc);
            //     $cbc = $xml->createElement('cbc:PaymentDueDate', date("Y-m-d", strtotime($value->FechaPago)));
            //     $cbc = $PaymentTerms->appendChild($cbc);
            // }
        }

        // TOTALES 
        $cac_TaxTotal = $xml->createElement('cac:TaxTotal');
        $cac_TaxTotal = $Invoice->appendChild($cac_TaxTotal);
        $cbc = $xml->createElement('cbc:TaxAmount', number_format(round($impuesto, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
        $cbc->setAttribute('currencyID', $venta->codiso);
        $cbc = $cac_TaxTotal->appendChild($cbc);

        if ($impuesto > 0) {
            $cac_TaxSubtotal = $xml->createElement('cac:TaxSubtotal');
            $cac_TaxSubtotal = $cac_TaxTotal->appendChild($cac_TaxSubtotal);
            $cbc = $xml->createElement('cbc:TaxableAmount', number_format(round($sub_total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $cac_TaxSubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $xml->createElement('cbc:TaxAmount', number_format(round($impuesto, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $cac_TaxSubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cac_TaxCategory = $xml->createElement('cac:TaxCategory');
            $cac_TaxCategory = $cac_TaxSubtotal->appendChild($cac_TaxCategory);
            $cac_TaxScheme = $xml->createElement('cac:TaxScheme');
            $cac_TaxScheme = $cac_TaxCategory->appendChild($cac_TaxScheme);
            $cbc = $xml->createElement('cbc:ID', '1000');
            $cbc = $cac_TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name', 'IGV');
            $cbc = $cac_TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT');
            $cbc = $cac_TaxScheme->appendChild($cbc);
        } else {
            $cac_TaxSubtotal = $xml->createElement('cac:TaxSubtotal');
            $cac_TaxSubtotal = $cac_TaxTotal->appendChild($cac_TaxSubtotal);
            $cbc = $xml->createElement('cbc:TaxableAmount', number_format(round($sub_total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $cac_TaxSubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $xml->createElement('cbc:TaxAmount', number_format(round($impuesto, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $cac_TaxSubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cac_TaxCategory = $xml->createElement('cac:TaxCategory');
            $cac_TaxCategory = $cac_TaxSubtotal->appendChild($cac_TaxCategory);
            $cac_TaxScheme = $xml->createElement('cac:TaxScheme');
            $cac_TaxScheme = $cac_TaxCategory->appendChild($cac_TaxScheme);
            $cbc = $xml->createElement('cbc:ID', '9997');
            $cbc = $cac_TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name', 'EXO');
            $cbc = $cac_TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT');
            $cbc = $cac_TaxScheme->appendChild($cbc);
        }

        // LEGAL MONETARY TOTAL  
        $cac_LegalMonetaryTotal = $xml->createElement('cac:LegalMonetaryTotal');
        $cac_LegalMonetaryTotal = $Invoice->appendChild($cac_LegalMonetaryTotal);
        $cbc = $xml->createElement('cbc:LineExtensionAmount', number_format(round($sub_total, 2, PHP_ROUND_HALF_UP), 2, '.', '')); //
        $cbc = $cac_LegalMonetaryTotal->appendChild($cbc);
        $cbc->setAttribute('currencyID',  $venta->codiso);
        $cbc = $xml->createElement('cbc:TaxInclusiveAmount', number_format(round($total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
        $cbc = $cac_LegalMonetaryTotal->appendChild($cbc);
        $cbc->setAttribute('currencyID',  $venta->codiso);
        $cbc = $xml->createElement('cbc:PayableAmount', number_format(round($total, 2, PHP_ROUND_HALF_UP), 2, '.', '')); //
        $cbc = $cac_LegalMonetaryTotal->appendChild($cbc);
        $cbc->setAttribute('currencyID',  $venta->codiso);

        $idlinea = 0;
        foreach ($detalle as $value) {
            $idlinea++;
            $cantidad = $value->cantidad;
            $impuesto = $value->porcentaje;
            $precioVenta = $value->precio;
            $igv = $impuesto / 100.00;

            $precioBruto = $precioVenta / ($igv  + 1);
            $impuestoGenerado = $precioBruto * $igv;

            $impuestoTotal = $impuestoGenerado * $cantidad;
            $importeBrutoTotal = $precioBruto * $cantidad;

            $importeNeto = $precioBruto + $impuestoGenerado;

            $InvoiceLine = $xml->createElement('cac:InvoiceLine');
            $InvoiceLine = $Invoice->appendChild($InvoiceLine);
            $cbc = $xml->createElement('cbc:ID', $idlinea);
            $cbc = $InvoiceLine->appendChild($cbc);
            $cbc = $xml->createElement('cbc:InvoicedQuantity', number_format(round($cantidad, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $InvoiceLine->appendChild($cbc);
            $cbc->setAttribute('unitCode', $value->codigoMedida);
            $cbc = $xml->createElement('cbc:LineExtensionAmount', number_format(round($importeBrutoTotal, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $InvoiceLine->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $pricing = $xml->createElement('cac:PricingReference');
            $pricing = $InvoiceLine->appendChild($pricing);
            $cac = $xml->createElement('cac:AlternativeConditionPrice');
            $cac = $pricing->appendChild($cac);
            $cbc = $xml->createElement('cbc:PriceAmount', number_format(round($importeNeto, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $cac->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $xml->createElement('cbc:PriceTypeCode', '01');
            $cbc = $cac->appendChild($cbc);

            $taxtotal = $xml->createElement('cac:TaxTotal');
            $taxtotal = $InvoiceLine->appendChild($taxtotal);
            $cbc = $xml->createElement('cbc:TaxAmount', number_format(round($impuestoTotal, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $taxtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $taxtsubtotal = $xml->createElement('cac:TaxSubtotal');
            $taxtsubtotal = $taxtotal->appendChild($taxtsubtotal);
            $cbc = $xml->createElement('cbc:TaxableAmount', number_format(round($importeBrutoTotal, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $taxtsubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $xml->createElement('cbc:TaxAmount', number_format(round($impuestoTotal, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $taxtsubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $taxtcategory = $xml->createElement('cac:TaxCategory');
            $taxtcategory = $taxtsubtotal->appendChild($taxtcategory);
            $cbc = $xml->createElement('cbc:Percent', round($impuesto, 0, PHP_ROUND_HALF_UP));
            $cbc = $taxtcategory->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxExemptionReasonCode', $value->codigo);
            $cbc = $taxtcategory->appendChild($cbc);


            if ($value->codigo == '10') {
                $igvcod = 'VAT';
                $igvnum = '1000';
                $igvname = 'IGV';
            } else {
                $igvcod = 'VAT';
                $igvnum = '9997';
                $igvname = 'EXO';
            }

            $taxscheme = $xml->createElement('cac:TaxScheme');
            $taxscheme = $taxtcategory->appendChild($taxscheme);
            $cbc = $xml->createElement('cbc:ID', $igvnum);
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name', $igvname);
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode', $igvcod);
            $cbc = $taxscheme->appendChild($cbc);

            $item = $xml->createElement('cac:Item');
            $item = $InvoiceLine->appendChild($item);
            $cbc = $xml->createElement('cbc:Description');
            $cbc->appendChild($xml->createCDATASection($value->producto));
            $cbc = $item->appendChild($cbc);
            $price = $xml->createElement('cac:Price');
            $price = $InvoiceLine->appendChild($price);
            $cbc = $xml->createElement('cbc:PriceAmount', number_format(round($precioBruto, 4, PHP_ROUND_HALF_UP), 4, '.', ''));
            $cbc = $price->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
        }

        //CREAR ARCHIVO
        $xml->formatOutput = true;

        return $xml;
    }

    public static function generateDailySummaryXml($venta, $detalle, $empresa, $correlativo, $currentDate)
    {
        $sub_total = array_reduce($detalle, function ($acumulador, $producto) {
            $igv = floatval($producto->porcentaje) / 100.00;
            $sub_total = floatval($producto->cantidad) * (floatval($producto->precio) / ($igv  + 1));
            return $acumulador + $sub_total;
        });

        $impuesto = array_reduce($detalle, function ($acumulador, $producto) {
            $igv = floatval($producto->porcentaje) / 100.00;
            $sub_total = floatval($producto->precio) / ($igv  + 1);
            $impuesto = floatval($producto->cantidad) * ($sub_total * $igv);
            return $acumulador + $impuesto;
        });

        $total = array_reduce($detalle, function ($acumulador, $producto) {
            return $acumulador + floatval($producto->cantidad) * floatval($producto->precio);
        });

        $xml = new DomDocument('1.0', 'utf-8');
        // $xml->standalone         = true;
        $xml->preserveWhiteSpace = false;

        $Invoice = $xml->createElement('SummaryDocuments');
        $Invoice = $xml->appendChild($Invoice);

        $Invoice->setAttribute('xmlns', 'urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1');
        $Invoice->setAttribute('xmlns:sac', 'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
        $Invoice->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $Invoice->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
        $Invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $Invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        $UBLExtension = $xml->createElement('ext:UBLExtensions');
        $UBLExtension = $Invoice->appendChild($UBLExtension);

        $ext = $xml->createElement('ext:UBLExtension');
        $ext = $UBLExtension->appendChild($ext);
        $contents = $xml->createElement('ext:ExtensionContent', ' ');
        $contents = $ext->appendChild($contents);

        $date = new DateTime($venta->fecha . "T" . $venta->hora);

        //Version de UBL 2.0
        $cbc = $xml->createElement('cbc:UBLVersionID', '2.0');
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:CustomizationID', '1.1');
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:ID', 'RC-' . $currentDate->format('Ymd') . '-' . $correlativo);
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:ReferenceDate', $date->format('Y-m-d'));
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:IssueDate', $currentDate->format('Y-m-d'));
        $cbc = $Invoice->appendChild($cbc);

        // DATOS DE FIRMA
        $cac_signature = $xml->createElement('cac:Signature');
        $cac_signature = $Invoice->appendChild($cac_signature);
        $cbc = $xml->createElement('cbc:ID',  $empresa->documento);
        $cbc = $cac_signature->appendChild($cbc);
        $cac_signatory = $xml->createElement('cac:SignatoryParty');
        $cac_signatory = $cac_signature->appendChild($cac_signatory);
        $cac = $xml->createElement('cac:PartyIdentification');
        $cac = $cac_signatory->appendChild($cac);
        $cbc = $xml->createElement('cbc:ID',  $empresa->documento);
        $cbc = $cac->appendChild($cbc);
        $cac = $xml->createElement('cac:PartyName');
        $cac = $cac_signatory->appendChild($cac);
        $cbc = $xml->createElement('cbc:Name');
        $cbc->appendChild($xml->createCDATASection($empresa->razonSocial));
        $cbc = $cac->appendChild($cbc);
        $cac = $xml->createElement('cac:ExternalReference');
        $cac_digital = $xml->createElement('cac:DigitalSignatureAttachment');
        $cac_digital = $cac_signature->appendChild($cac_digital);
        $cac = $cac_digital->appendChild($cac);
        $cbc = $xml->createElement('cbc:URI', '#SysSoftIntegra');
        $cbc = $cac->appendChild($cbc);

        // DATOS EMISOR
        $cac_SupplierParty = $xml->createElement('cac:AccountingSupplierParty');
        $cac_SupplierParty = $Invoice->appendChild($cac_SupplierParty);
        $CustomerAssignedAccountID = $xml->createElement('cbc:CustomerAssignedAccountID', $empresa->documento);
        $CustomerAssignedAccountID = $cac_SupplierParty->appendChild($CustomerAssignedAccountID);
        $AdditionalAccountID = $xml->createElement('cbc:AdditionalAccountID', $empresa->codigo);
        $AdditionalAccountID = $cac_SupplierParty->appendChild($AdditionalAccountID);
        $cac_party = $xml->createElement('cac:Party');
        $cac_party = $cac_SupplierParty->appendChild($cac_party);
        $PartyLegalEntity = $xml->createElement('cac:PartyLegalEntity');
        $PartyLegalEntity = $cac_party->appendChild($PartyLegalEntity);
        $cbc = $xml->createElement('cbc:RegistrationName');
        $cbc->appendChild($xml->createCDATASection($empresa->nombreEmpresa));
        $cbc = $PartyLegalEntity->appendChild($cbc);

        // DOCUMENTO ASOCIADO
        $SummaryDocumentsLine = $xml->createElement('sac:SummaryDocumentsLine');
        $SummaryDocumentsLine = $Invoice->appendChild($SummaryDocumentsLine);
        $LineID = $xml->createElement('cbc:LineID', '1');
        $LineID = $SummaryDocumentsLine->appendChild($LineID);
        $DocumentTypeCode = $xml->createElement('cbc:DocumentTypeCode', $venta->codigoVenta);
        $DocumentTypeCode = $SummaryDocumentsLine->appendChild($DocumentTypeCode);
        $ID = $xml->createElement('cbc:ID', $venta->serie . '-' . $venta->numeracion);
        $ID = $SummaryDocumentsLine->appendChild($ID);

        $AccountingCustomerParty = $xml->createElement('cac:AccountingCustomerParty');
        $AccountingCustomerParty = $SummaryDocumentsLine->appendChild($AccountingCustomerParty);
        $CustomerAssignedAccountID = $xml->createElement('cbc:CustomerAssignedAccountID', $venta->documento);
        $CustomerAssignedAccountID = $AccountingCustomerParty->appendChild($CustomerAssignedAccountID);
        $AdditionalAccountID = $xml->createElement('cbc:AdditionalAccountID', $venta->codigoCliente);
        $AdditionalAccountID = $AccountingCustomerParty->appendChild($AdditionalAccountID);

        $Status = $xml->createElement('cac:Status');
        $Status = $SummaryDocumentsLine->appendChild($Status);
        $ConditionCode = $xml->createElement('cbc:ConditionCode', '3');
        $ConditionCode = $Status->appendChild($ConditionCode);

        $TotalAmount = $xml->createElement('sac:TotalAmount', number_format(round($total, 2, PHP_ROUND_HALF_UP), 2, '.', '')); //
        $TotalAmount = $SummaryDocumentsLine->appendChild($TotalAmount);
        $TotalAmount->setAttribute('currencyID',  $venta->codiso);

        if ($impuesto > 0) {
            $BillingPayment = $xml->createElement('sac:BillingPayment');
            $cbc = $xml->createElement('cbc:PaidAmount', number_format(round($sub_total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $BillingPayment->appendChild($cbc);

            $cbc = $xml->createElement('cbc:InstructionID', '01');
            $cbc = $BillingPayment->appendChild($cbc);

            $BillingPayment = $SummaryDocumentsLine->appendChild($BillingPayment);
        } else {
            $BillingPayment = $xml->createElement('sac:BillingPayment');
            $cbc = $xml->createElement('cbc:PaidAmount', number_format(round($sub_total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $BillingPayment->appendChild($cbc);

            $cbc = $xml->createElement('cbc:InstructionID', '02');
            $cbc = $BillingPayment->appendChild($cbc);

            $BillingPayment = $SummaryDocumentsLine->appendChild($BillingPayment);
        }

        $cac_TaxTotal = $xml->createElement('cac:TaxTotal');
        $cac_TaxTotal = $SummaryDocumentsLine->appendChild($cac_TaxTotal);
        $cbc = $xml->createElement('cbc:TaxAmount', number_format(round($impuesto, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
        $cbc->setAttribute('currencyID', $venta->codiso);
        $cbc = $cac_TaxTotal->appendChild($cbc);

        if ($impuesto > 0) {
            $cac_TaxSubtotal = $xml->createElement('cac:TaxSubtotal');
            $cac_TaxSubtotal = $cac_TaxTotal->appendChild($cac_TaxSubtotal);
            $cbc = $xml->createElement('cbc:TaxableAmount', number_format(round($sub_total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $cac_TaxSubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $xml->createElement('cbc:TaxAmount', number_format(round($impuesto, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $cac_TaxSubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cac_TaxCategory = $xml->createElement('cac:TaxCategory');
            $cac_TaxCategory = $cac_TaxSubtotal->appendChild($cac_TaxCategory);
            $cac_TaxScheme = $xml->createElement('cac:TaxScheme');
            $cac_TaxScheme = $cac_TaxCategory->appendChild($cac_TaxScheme);
            $cbc = $xml->createElement('cbc:ID', '1000');
            $cbc = $cac_TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name', 'IGV');
            $cbc = $cac_TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT');
            $cbc = $cac_TaxScheme->appendChild($cbc);
        } else {
            $cac_TaxSubtotal = $xml->createElement('cac:TaxSubtotal');
            $cac_TaxSubtotal = $cac_TaxTotal->appendChild($cac_TaxSubtotal);
            $cbc = $xml->createElement('cbc:TaxableAmount', number_format(round($sub_total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $cac_TaxSubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cbc = $xml->createElement('cbc:TaxAmount', number_format(round($impuesto, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
            $cbc = $cac_TaxSubtotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $venta->codiso);
            $cac_TaxCategory = $xml->createElement('cac:TaxCategory');
            $cac_TaxCategory = $cac_TaxSubtotal->appendChild($cac_TaxCategory);
            $cac_TaxScheme = $xml->createElement('cac:TaxScheme');
            $cac_TaxScheme = $cac_TaxCategory->appendChild($cac_TaxScheme);
            $cbc = $xml->createElement('cbc:ID', '9997');
            $cbc = $cac_TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name', 'EXO');
            $cbc = $cac_TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT');
            $cbc = $cac_TaxScheme->appendChild($cbc);
        }

        $xml->formatOutput = true;

        return $xml;
    }

    public static function generateDespatchAdviceXml($guiaRemision, $detalle, $empresa)
    {
        $fechaRegistro = new DateTime($guiaRemision->fecha . "T" . $guiaRemision->hora);

        $fechaTraslado = new DateTime($guiaRemision->fechaTraslado . "T" . $guiaRemision->hora);


        $xml = new DomDocument('1.0', 'utf-8');
        // $xml->standalone         = true;
        $xml->preserveWhiteSpace = false;

        $Invoice = $xml->createElement('DespatchAdvice');
        $Invoice = $xml->appendChild($Invoice);

        $Invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2');
        $Invoice->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
        $Invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $Invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $Invoice->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');

        $UBLExtension = $xml->createElement('ext:UBLExtensions');
        $UBLExtension = $Invoice->appendChild($UBLExtension);

        $ext = $xml->createElement('ext:UBLExtension');
        $ext = $UBLExtension->appendChild($ext);
        $contents = $xml->createElement('ext:ExtensionContent', ' ');
        $contents = $ext->appendChild($contents);

        //Version de UBL 2.1
        $cbc = $xml->createElement('cbc:UBLVersionID', '2.1');
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:CustomizationID', '2.0');
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:ID', $guiaRemision->serie . "-" . $guiaRemision->numeracion);
        $cbc = $Invoice->appendChild($cbc);

        // FECHA Y HORA DE EMISIÓN
        $cbc = $xml->createElement('cbc:IssueDate', $fechaRegistro->format('Y-m-d'));
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:IssueTime', $fechaRegistro->format('H:i:s'));
        $cbc = $Invoice->appendChild($cbc);
        $cbc = $xml->createElement('cbc:DespatchAdviceTypeCode', $guiaRemision->codigo);
        $cbc->setAttribute('listAgencyName', "PE:SUNAT");
        $cbc->setAttribute('listName', "Tipo de Documento");
        $cbc->setAttribute('listURI', "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01");
        $cbc = $Invoice->appendChild($cbc);

        // DOCUMENTOS RELACIONADOS (Catalogo 41)
        $cac_additional_document_reference = $xml->createElement('cac:AdditionalDocumentReference');
        $cac_additional_document_reference = $Invoice->appendChild($cac_additional_document_reference);

        $cbc = $xml->createElement('cbc:ID', $guiaRemision->serieRef . "-" . $guiaRemision->numeracionRef);
        $cbc = $cac_additional_document_reference->appendChild($cbc);

        $cbc = $xml->createElement('cbc:DocumentTypeCode', $guiaRemision->codigoComprobanteRef);
        $cbc->setAttribute('listAgencyName', "PE:SUNAT");
        $cbc->setAttribute('listName', "Documento relacionado al transporte");
        $cbc->setAttribute('listURI', "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo61");
        $cbc = $cac_additional_document_reference->appendChild($cbc);

        $cbc = $xml->createElement('cbc:DocumentType', $guiaRemision->nombreComprobanteRef);
        $cbc = $cac_additional_document_reference->appendChild($cbc);

        $cac_issuer_party = $xml->createElement('cac:IssuerParty');
        $cac_issuer_party = $cac_additional_document_reference->appendChild($cac_issuer_party);

        $cac_party_identification = $xml->createElement('cac:PartyIdentification');
        $cac_party_identification = $cac_issuer_party->appendChild($cac_party_identification);

        $cbc = $xml->createElement('cbc:ID', $empresa->documento);
        $cbc->setAttribute('schemeID', $empresa->codigo);
        $cbc->setAttribute('schemeAgencyName', "PE:SUNAT");
        $cbc->setAttribute('schemeName', "Documento de Identidad");
        $cbc->setAttribute('schemeURI', "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06");
        $cbc = $cac_party_identification->appendChild($cbc);

        // DATOS DE FIRMA
        $cac_signature = $xml->createElement('cac:Signature');
        $cac_signature = $Invoice->appendChild($cac_signature);
        $cbc = $xml->createElement('cbc:ID', $empresa->documento);
        $cbc = $cac_signature->appendChild($cbc);
        $cac_signatory = $xml->createElement('cac:SignatoryParty');
        $cac_signatory = $cac_signature->appendChild($cac_signatory);
        $cac = $xml->createElement('cac:PartyIdentification');
        $cac = $cac_signatory->appendChild($cac);
        $cbc = $xml->createElement('cbc:ID', $empresa->documento);
        $cbc = $cac->appendChild($cbc);
        $cac = $xml->createElement('cac:PartyName');
        $cac = $cac_signatory->appendChild($cac);
        $cbc = $xml->createElement('cbc:Name');
        $cbc->appendChild($xml->createCDATASection($empresa->razonSocial));
        $cbc = $cac->appendChild($cbc);
        $cac = $xml->createElement('cac:ExternalReference');
        $cac_digital = $xml->createElement('cac:DigitalSignatureAttachment');
        $cac_digital = $cac_signature->appendChild($cac_digital);
        $cac = $cac_digital->appendChild($cac);
        $cbc = $xml->createElement('cbc:URI', '#SysSoftIntegra');
        $cbc = $cac->appendChild($cbc);

        // DATOS DEL EMISOR (REMITENTE)
        $cac_despatch_supplier_party = $xml->createElement('cac:DespatchSupplierParty');
        $cac_despatch_supplier_party = $Invoice->appendChild($cac_despatch_supplier_party);
        $cac_party = $xml->createElement('cac:Party');
        $cac_party = $cac_despatch_supplier_party->appendChild($cac_party);

        $cac_party_identification = $xml->createElement('cac:PartyIdentification');
        $cbc = $xml->createElement('cbc:ID', $empresa->documento);
        $cbc->setAttribute('schemeID', $empresa->codigo);
        $cbc->setAttribute('schemeName', "Documento de Identidad");
        $cbc->setAttribute('schemeAgencyName', "PE:SUNAT");
        $cbc->setAttribute('schemeURI', "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06");
        $cbc = $cac_party_identification->appendChild($cbc);
        $cac_party_identification = $cac_party->appendChild($cac_party_identification);

        $cac_party_legal_entity = $xml->createElement('cac:PartyLegalEntity');
        $cbc = $xml->createElement('cbc:RegistrationName');
        $cbc->appendChild($xml->createCDATASection($empresa->nombreEmpresa));
        $cbc = $cac_party_legal_entity->appendChild($cbc);
        $cac_party_legal_entity = $cac_party->appendChild($cac_party_legal_entity);

        // DATOS DEL RECEPTOR (DESTINATARIO)
        $cac_delivery_customer_party = $xml->createElement('cac:DeliveryCustomerParty');
        $cac_delivery_customer_party = $Invoice->appendChild($cac_delivery_customer_party);
        $cac_party = $xml->createElement('cac:Party');
        $cac_party = $cac_delivery_customer_party->appendChild($cac_party);

        $cac_party_identification = $xml->createElement('cac:PartyIdentification');
        $cbc = $xml->createElement('cbc:ID', $guiaRemision->documentoDestino);
        $cbc->setAttribute('schemeID', $guiaRemision->codDestino);
        $cbc->setAttribute('schemeName', "Documento de Identidad");
        $cbc->setAttribute('schemeAgencyName', "PE:SUNAT");
        $cbc->setAttribute('schemeURI', "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06");
        $cbc = $cac_party_identification->appendChild($cbc);
        $cac_party_identification = $cac_party->appendChild($cac_party_identification);

        $cac_party_legal_entity = $xml->createElement('cac:PartyLegalEntity');
        $cbc = $xml->createElement('cbc:RegistrationName');
        $cbc->appendChild($xml->createCDATASection($guiaRemision->informacionDestino));
        $cbc = $cac_party_legal_entity->appendChild($cbc);
        $cac_party_legal_entity = $cac_party->appendChild($cac_party_legal_entity);

        // DATOS DE TRASLADO
        $cac_shipment = $xml->createElement('cac:Shipment');
        $cac_shipment = $Invoice->appendChild($cac_shipment);

        // ID OBLIGATORIO POR UBL
        $cbc = $xml->createElement('cbc:ID', "SUNAT_Envio");
        $cbc = $cac_shipment->appendChild($cbc);

        // MOTIVO DEL TRASLADO
        $cbc = $xml->createElement('cbc:HandlingCode', $guiaRemision->codigoMotivoTraslado);
        $cbc->setAttribute('listAgencyName', "PE:SUNAT");
        $cbc->setAttribute('listName', "Motivo de traslado");
        $cbc->setAttribute('listURI', "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo20");
        $cbc = $cac_shipment->appendChild($cbc);

        $cbc = $xml->createElement('cbc:HandlingInstructions', $guiaRemision->nombreMotivoTraslado);
        $cbc = $cac_shipment->appendChild($cbc);

        // PESO BRUTO TOTAL DE LA CARGA
        $cbc = $xml->createElement('cbc:GrossWeightMeasure', $guiaRemision->peso);
        $cbc->setAttribute('unitCode', $guiaRemision->codigoTipoPeso);
        $cbc = $cac_shipment->appendChild($cbc);

        if ($guiaRemision->codigoModalidadTraslado == "01") {
            /**
             * INDICADORES
             */
            $cac_shipment_stage = $xml->createElement('cac:ShipmentStage');
            $cac_shipment_stage = $cac_shipment->appendChild($cac_shipment_stage);

            // MODALIDAD DE TRANSPORTE
            $cbc = $xml->createElement('cbc:TransportModeCode', $guiaRemision->codigoModalidadTraslado);
            $cbc->setAttribute('listName', "Modalidad de traslado");
            $cbc->setAttribute('listAgencyName', "PE:SUNAT");
            $cbc->setAttribute('listURI', "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo18");
            $cbc = $cac_shipment_stage->appendChild($cbc);

            // FECHA DE TRASLADO
            $cac_transit_period = $xml->createElement('cac:TransitPeriod');
            $cac_transit_period = $cac_shipment_stage->appendChild($cac_transit_period);
            $cbc = $xml->createElement('cbc:StartDate', $fechaTraslado->format('Y-m-d'));
            $cbc = $cac_transit_period->appendChild($cbc);

            // EMPRESA PÚBLICA QUE LLEVA LA MERCADERÍA
            $cac_carrier_party = $xml->createElement('cac:CarrierParty');
            $cac_carrier_party = $cac_shipment_stage->appendChild($cac_carrier_party);

            $cac_party_identification = $xml->createElement('cac:PartyIdentification');
            $cbc = $xml->createElement('cbc:ID', $guiaRemision->documentoConductor);
            $cbc->setAttribute('schemeID', $guiaRemision->codigoConductor);
            $cbc = $cac_party_identification->appendChild($cbc);
            $cac_party_identification = $cac_carrier_party->appendChild($cac_party_identification);

            $cac_party_legal_entity = $xml->createElement('cac:PartyLegalEntity');
            $cbc = $xml->createElement('cbc:RegistrationName');
            $cbc->appendChild($xml->createCDATASection($guiaRemision->informacionConductor));
            $cbc = $cac_party_legal_entity->appendChild($cbc);
            $cbc = $xml->createElement('cbc:CompanyID', '0001');
            $cbc = $cac_party_legal_entity->appendChild($cbc);
            $cac_party_legal_entity = $cac_carrier_party->appendChild($cac_party_legal_entity);

            /**
             * DIRECCION Y UBIGEO DEL COMPROBANTE
             */
            $cac_delivery = $xml->createElement('cac:Delivery');
            $cac_delivery = $cac_shipment->appendChild($cac_delivery);

            // DIRECCION DEL PUNTO DE LLEGADA
            $cac_delivery_address = $xml->createElement('cac:DeliveryAddress');
            $cbc = $xml->createElement('cbc:ID', $guiaRemision->ubigeoLlegada);
            $cbc->setAttribute('schemeAgencyName', "PE:INEI");
            $cbc->setAttribute('schemeName', "Ubigeos");
            $cbc = $cac_delivery_address->appendChild($cbc);
            $cac_address_line = $xml->createElement('cac:AddressLine');
            $cbc = $xml->createElement('cbc:Line', $guiaRemision->direccionLlegada);
            $cbc = $cac_address_line->appendChild($cbc);
            $cac_address_line = $cac_delivery_address->appendChild($cac_address_line);
            $cac_delivery_address = $cac_delivery->appendChild($cac_delivery_address);

            // DIRECCION DE PUNTO DE PARTIDA
            $cac_despatch = $xml->createElement('cac:Despatch');
            $cac_despatch = $cac_delivery->appendChild($cac_despatch);
            $cac_despatch_address = $xml->createElement('cac:DespatchAddress');
            $cbc = $xml->createElement('cbc:ID', $guiaRemision->ubigeoPartida);
            $cbc->setAttribute('schemeAgencyName', "PE:INEI");
            $cbc->setAttribute('schemeName', "Ubigeos");
            $cbc = $cac_despatch_address->appendChild($cbc);
            $cac_address_line = $xml->createElement('cac:AddressLine');
            $cbc = $xml->createElement('cbc:Line', $guiaRemision->direccionPartida);
            $cbc = $cac_address_line->appendChild($cbc);
            $cac_address_line = $cac_despatch_address->appendChild($cac_address_line);
            $cac_despatch_address = $cac_despatch->appendChild($cac_despatch_address);

            /**
             * Transporte
             */
            $cac_transport_handling_unit = $xml->createElement('cac:TransportHandlingUnit');
            $cac_transport_handling_unit = $cac_shipment->appendChild($cac_transport_handling_unit);
        } else {
            /**
             * INDICADORES
             */
            $cac_shipment_stage = $xml->createElement('cac:ShipmentStage');
            $cac_shipment_stage = $cac_shipment->appendChild($cac_shipment_stage);

            // MODALIDAD DE TRANSPORTE
            $cbc = $xml->createElement('cbc:TransportModeCode', $guiaRemision->codigoModalidadTraslado);
            $cbc->setAttribute('listName', "Modalidad de traslado");
            $cbc->setAttribute('listAgencyName', "PE:SUNAT");
            $cbc->setAttribute('listURI', "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo18");
            $cbc = $cac_shipment_stage->appendChild($cbc);

            // FECHA DE TRASLADO
            $cac_transit_period = $xml->createElement('cac:TransitPeriod');
            $cac_transit_period = $cac_shipment_stage->appendChild($cac_transit_period);
            $cbc = $xml->createElement('cbc:StartDate', $fechaTraslado->format('Y-m-d'));
            $cbc = $cac_transit_period->appendChild($cbc);

            /**
             * CONDUCTOR PRINCIPAL
             */
            $cac_driver_person = $xml->createElement('cac:DriverPerson');
            $cac_driver_person = $cac_shipment_stage->appendChild($cac_driver_person);

            // TIPO Y NUMERO DE DOCUMENTO DE IDENTIDAD
            $cbc = $xml->createElement('cbc:ID', $guiaRemision->documentoConductor);
            $cbc->setAttribute('schemeID', "1");
            $cbc->setAttribute('schemeName', "Documento de Identidad");
            $cbc->setAttribute('schemeAgencyName', "PE:SUNAT");
            $cbc->setAttribute('schemeURI', "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06");
            $cbc = $cac_driver_person->appendChild($cbc);

            // NOMBRES
            $cbc = $xml->createElement('cbc:FirstName', $guiaRemision->informacionConductor);
            $cbc = $cac_driver_person->appendChild($cbc);

            // APELLIDOS
            $cbc = $xml->createElement('cbc:FamilyName', $guiaRemision->informacionConductor);
            $cbc = $cac_driver_person->appendChild($cbc);

            // TIPO DE CONDUCTOR: PRINCIPAL
            $cbc = $xml->createElement('cbc:JobTitle', "Principal");
            $cbc = $cac_driver_person->appendChild($cbc);

            /**
             * DIRECCION Y UBIGEO DEL COMPROBANTE
             */
            $cac_delivery = $xml->createElement('cac:Delivery');
            $cac_delivery = $cac_shipment->appendChild($cac_delivery);

            // DIRECCION DEL PUNTO DE LLEGADA
            $cac_delivery_address = $xml->createElement('cac:DeliveryAddress');
            $cbc = $xml->createElement('cbc:ID', $guiaRemision->ubigeoLlegada);
            $cbc->setAttribute('schemeAgencyName', "PE:INEI");
            $cbc->setAttribute('schemeName', "Ubigeos");
            $cbc = $cac_delivery_address->appendChild($cbc);
            $cac_address_line = $xml->createElement('cac:AddressLine');
            $cbc = $xml->createElement('cbc:Line', $guiaRemision->direccionLlegada);
            $cbc = $cac_address_line->appendChild($cbc);
            $cac_address_line = $cac_delivery_address->appendChild($cac_address_line);
            $cac_delivery_address = $cac_delivery->appendChild($cac_delivery_address);

            // DIRECCION DE PUNTO DE PARTIDA
            $cac_despatch = $xml->createElement('cac:Despatch');
            $cac_despatch = $cac_delivery->appendChild($cac_despatch);
            $cac_despatch_address = $xml->createElement('cac:DespatchAddress');
            $cbc = $xml->createElement('cbc:ID', $guiaRemision->ubigeoPartida);
            $cbc->setAttribute('schemeAgencyName', "PE:INEI");
            $cbc->setAttribute('schemeName', "Ubigeos");
            $cbc = $cac_despatch_address->appendChild($cbc);
            $cac_address_line = $xml->createElement('cac:AddressLine');
            $cbc = $xml->createElement('cbc:Line', $guiaRemision->direccionPartida);
            $cbc = $cac_address_line->appendChild($cbc);
            $cac_address_line = $cac_despatch_address->appendChild($cac_address_line);
            $cac_despatch_address = $cac_despatch->appendChild($cac_despatch_address);

            /**
             * LICENCIA DE CONDUCIR
             */
            $cac_identity_document_reference = $xml->createElement('cac:IdentityDocumentReference');
            $cac_identity_document_reference = $cac_driver_person->appendChild($cac_identity_document_reference);

            // NÚMERO DE LICENCIA
            $cbc = $xml->createElement('cbc:ID', $guiaRemision->licenciaConducir);
            $cbc = $cac_identity_document_reference->appendChild($cbc);

            /**
             * Transporte
             */
            $cac_transport_handling_unit = $xml->createElement('cac:TransportHandlingUnit');
            $cac_transport_handling_unit = $cac_shipment->appendChild($cac_transport_handling_unit);

            $cac_transport_equipment = $xml->createElement('cac:TransportEquipment');
            //
            $cbc = $xml->createElement('cbc:ID', $guiaRemision->numeroPlaca);
            $cbc = $cac_transport_equipment->appendChild($cbc);

            $cac_transport_equipment = $cac_transport_handling_unit->appendChild($cac_transport_equipment);
        }

        // DETALLES DE BIENES A TRASLADAR
        $index = 0;
        foreach ($detalle as $value) {
            $index++;

            $cac_despatch_line = $xml->createElement('cac:DespatchLine');
            $cbc = $xml->createElement('cbc:ID', $index);
            $cbc = $cac_despatch_line->appendChild($cbc);
            $cbc = $xml->createElement('cbc:DeliveredQuantity', $value->cantidad);
            $cbc->setAttribute('unitCode', $value->codigoMedida);
            $cbc = $cac_despatch_line->appendChild($cbc);
        
            $cac_order_line_reference = $xml->createElement('cac:OrderLineReference');
            $cbc = $xml->createElement('cbc:LineID', $index);
            $cbc = $cac_order_line_reference->appendChild($cbc);
            $cac_order_line_reference = $cac_despatch_line->appendChild($cac_order_line_reference);
        
            $cac_item = $xml->createElement('cac:Item');
            $cbc = $xml->createElement('cbc:Description');
            $cbc->appendChild($xml->createCDATASection($value->nombre));
            $cbc = $cac_item->appendChild($cbc);
        
            $cac_sellers_item_identification = $xml->createElement('cac:SellersItemIdentification');
            $cbc = $xml->createElement('cbc:ID', $value->idProducto);
            $cbc = $cac_sellers_item_identification->appendChild($cbc);
            $cac_sellers_item_identification = $cac_item->appendChild($cac_sellers_item_identification);
        
            $cac_item = $cac_despatch_line->appendChild($cac_item);
            $cac_despatch_line = $Invoice->appendChild($cac_despatch_line);
        }   

        //CREAR ARCHIVO
        $xml->formatOutput = true;

        return $xml;
    }
}
